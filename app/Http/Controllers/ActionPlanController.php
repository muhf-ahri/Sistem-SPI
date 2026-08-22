<?php

namespace App\Http\Controllers;

use App\Models\ActionPlan;
use App\Models\Finding;
use App\Models\User;
use App\Http\Requests\StoreActionPlanRequest;
use App\Http\Requests\UpdateActionPlanRequest;
use App\Helpers\AuditLogHelper;
use Illuminate\Http\Request;

class ActionPlanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ActionPlan::class, 'action_plan');
    }

    public function index(Request $request)
    {
        $query = ActionPlan::with(['finding.auditPlan.division', 'pic']);

        if (auth()->user()->role === 'kepala_divisi') {
            $query->whereHas('finding.auditPlan', function ($q) {
                $q->where('division_id', auth()->user()->division_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $actionPlans = $query->orderBy('created_at', 'desc')->paginate(10);
        $statuses = ['pending', 'in_progress', 'submitted', 'verified', 'rejected', 'completed'];
        return view('action-plans.index', compact('actionPlans', 'statuses'));
    }

    public function create(Request $request)
    {
        $findingId = $request->query('finding_id');
        $finding = Finding::findOrFail($findingId);
        // Hanya PIC yang bisa dipilih dari divisi tersebut
        $pics = User::where('division_id', $finding->auditPlan->division_id)
            ->where('is_active', true)
            ->pluck('name', 'id');
        return view('action-plans.create', compact('finding', 'pics'));
    }

    public function store(StoreActionPlanRequest $request)
    {
        $validated = $request->validated();
        $actionPlan = ActionPlan::create($validated);

        AuditLogHelper::log('create', 'action_plan', $actionPlan->id, null, $actionPlan->toArray());

        return redirect()->route('findings.show', $actionPlan->finding_id)
            ->with('success', 'Rencana tindak lanjut berhasil dibuat.');
    }

    public function show(ActionPlan $actionPlan)
    {
        $actionPlan->load(['finding', 'pic', 'followUpEvidences', 'verifications']);
        return view('action-plans.show', compact('actionPlan'));
    }

    public function edit(ActionPlan $actionPlan)
    {
        $pics = User::where('division_id', $actionPlan->finding->auditPlan->division_id)
            ->where('is_active', true)
            ->pluck('name', 'id');
        return view('action-plans.edit', compact('actionPlan', 'pics'));
    }

    public function update(UpdateActionPlanRequest $request, ActionPlan $actionPlan)
    {
        $old = $actionPlan->toArray();
        $actionPlan->update($request->validated());
        AuditLogHelper::log('update', 'action_plan', $actionPlan->id, $old, $actionPlan->toArray());
        return redirect()->route('findings.show', $actionPlan->finding_id)
            ->with('success', 'Rencana tindak lanjut diperbarui.');
    }

    public function destroy(ActionPlan $actionPlan)
    {
        $findingId = $actionPlan->finding_id;
        $actionPlan->delete();
        AuditLogHelper::log('delete', 'action_plan', $actionPlan->id, $actionPlan->toArray(), null);
        return redirect()->route('findings.show', $findingId)
            ->with('success', 'Rencana tindak lanjut dihapus.');
    }

    // Custom: submit untuk verifikasi
    public function submitVerification(ActionPlan $actionPlan)
    {
        $this->authorize('submitForVerification', $actionPlan);
        $actionPlan->status = 'submitted';
        $actionPlan->save();
        AuditLogHelper::log('submit_verification', 'action_plan', $actionPlan->id, ['status' => 'in_progress'], ['status' => 'submitted']);
        return redirect()->route('findings.show', $actionPlan->finding_id)
            ->with('success', 'Rencana tindak lanjut dikirim untuk verifikasi.');
    }

    // Custom: verifikasi oleh SPI
    public function verify(Request $request, ActionPlan $actionPlan)
    {
        $this->authorize('verify', $actionPlan);
        $request->validate([
            'result' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $old = $actionPlan->toArray();
        $actionPlan->status = $request->result === 'approved' ? 'verified' : 'rejected';
        $actionPlan->save();

        // Simpan verifikasi
        \App\Models\Verification::create([
            'action_plan_id' => $actionPlan->id,
            'verifier_id' => auth()->id(),
            'result' => $request->result,
            'notes' => $request->notes,
            'verified_at' => now(),
        ]);

        // Jika approved, update finding status ke closed
        if ($request->result === 'approved') {
            $finding = $actionPlan->finding;
            $finding->status = 'closed';
            $finding->save();
            AuditLogHelper::log('close_finding', 'finding', $finding->id, ['status' => 'waiting_verification'], ['status' => 'closed']);
        } else {
            // Jika rejected, kembalikan finding ke in_progress
            $finding = $actionPlan->finding;
            $finding->status = 'in_progress';
            $finding->save();
        }

        AuditLogHelper::log('verify_action_plan', 'action_plan', $actionPlan->id, $old, $actionPlan->toArray());

        return redirect()->route('findings.show', $actionPlan->finding_id)
            ->with('success', 'Verifikasi selesai.');
    }

    public function uploadEvidence(Request $request, ActionPlan $actionPlan)
    {
        if (auth()->id() !== $actionPlan->pic_user_id && !in_array(auth()->user()->role, ['super_admin', 'spi'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'evidence_file' => 'required|file|max:10240', // max 10MB
        ]);

        if ($request->hasFile('evidence_file')) {
            $file = $request->file('evidence_file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('evidences/follow_ups', 'public');
            
            \App\Models\FollowUpEvidence::create([
                'action_plan_id' => $actionPlan->id,
                'uploaded_by' => auth()->id(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);

            if ($actionPlan->status === 'pending') {
                $old = $actionPlan->toArray();
                $actionPlan->status = 'in_progress';
                $actionPlan->save();
                AuditLogHelper::logStatusChange('action_plan', $actionPlan->id, 'pending', 'in_progress');
            }

            AuditLogHelper::logUpload('action_plan', $actionPlan->id, $filePath);

            return back()->with('success', 'Bukti tindak lanjut berhasil diupload.');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }
}