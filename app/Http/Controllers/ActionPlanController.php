<?php

namespace App\Http\Controllers;

use App\Models\ActionPlan;
use App\Models\Finding;
use App\Models\User;
use App\Http\Requests\StoreActionPlanRequest;
use App\Http\Requests\UpdateActionPlanRequest;
use App\Helpers\AuditLogHelper;
use App\Services\NotificationService;
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
        // Data scoping: kepala divisi hanya menindaklanjuti temuan divisinya
        if (auth()->user()->role === 'kepala_divisi'
            && auth()->user()->division_id !== $finding->auditPlan->division_id) {
            abort(403, 'Unauthorized action.');
        }
        // Hanya PIC yang bisa dipilih dari divisi tersebut
        $pics = User::where('division_id', $finding->auditPlan->division_id)
            ->where('is_active', true)
            ->pluck('name', 'id');
        return view('action-plans.create', compact('finding', 'pics'));
    }

    public function store(StoreActionPlanRequest $request)
    {
        $finding = Finding::findOrFail($request->input('finding_id'));
        // Data scoping: kepala divisi hanya menindaklanjuti temuan divisinya
        if (auth()->user()->role === 'kepala_divisi'
            && auth()->user()->division_id !== $finding->auditPlan->division_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();
        // Alur §15: action plan baru berstatus pending
        $validated['status'] = 'pending';
        $actionPlan = ActionPlan::create($validated);

        // Alur §14: temuan mulai dikerjakan divisi (open -> in_progress)
        $finding = $actionPlan->finding;
        if ($finding->status === 'open') {
            $findingOld = $finding->status;
            $finding->status = 'in_progress';
            $finding->save();
            AuditLogHelper::logStatusChange('finding', $finding->id, $findingOld, 'in_progress');
        }

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
        // Status tidak boleh diubah manual; hanya melalui submit/upload/verifikasi
        $data = collect($request->validated())->except(['status'])->all();
        $actionPlan->update($data);
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
        $apOld = $actionPlan->status;
        $actionPlan->status = 'submitted';
        $actionPlan->save();
        AuditLogHelper::logStatusChange('action_plan', $actionPlan->id, $apOld, 'submitted');

        // Alur §14: temuan menunggu verifikasi SPI
        $finding = $actionPlan->finding;
        if (!in_array($finding->status, ['waiting_verification', 'closed'])) {
            $findingOld = $finding->status;
            $finding->status = 'waiting_verification';
            $finding->save();
            AuditLogHelper::logStatusChange('finding', $finding->id, $findingOld, 'waiting_verification');
            
            // Notify SPI
            NotificationService::sendToRoles(
                ['spi'],
                'Verifikasi Tindak Lanjut',
                'Tindak lanjut untuk temuan ' . $finding->finding_number . ' telah dikirim untuk verifikasi.',
                route('findings.show', $finding->id),
                'info'
            );
        }

        return redirect()->route('findings.show', $actionPlan->finding_id)
            ->with('success', 'Rencana tindak lanjut dikirim untuk verifikasi.');
    }

    // Custom: verifikasi oleh SPI
    public function verify(Request $request, ActionPlan $actionPlan)
    {
        $this->authorize('verify', $actionPlan);
        $request->validate([
            'result' => 'required|in:approved,rejected',
            // Alur §16: penolakan wajib disertai catatan agar divisi tahu apa yang perlu diperbaiki
            'notes' => 'required_if:result,rejected|nullable|string',
        ], [
            'notes.required_if' => 'Catatan verifikasi wajib diisi jika menolak.',
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

        AuditLogHelper::log('verify_action_plan', 'action_plan', $actionPlan->id, $old, $actionPlan->toArray());

        $finding = $actionPlan->finding;
        if ($request->result === 'approved') {
            // Alur §16: disetujui -> temuan closed
            $findingOld = $finding->status;
            $finding->status = 'closed';
            $finding->save();
            AuditLogHelper::logStatusChange('finding', $finding->id, $findingOld, 'closed');
            
            NotificationService::sendToUsers(
                $actionPlan->pic_user_id,
                'Tindak Lanjut Disetujui',
                'Tindak lanjut untuk temuan ' . $finding->finding_number . ' telah disetujui.',
                route('findings.show', $finding->id),
                'success'
            );
        } else {
            // Alur §14: ditolak -> temuan kembali ke status rejected untuk diperbaiki divisi
            $findingOld = $finding->status;
            $finding->status = 'rejected';
            $finding->save();
            AuditLogHelper::logStatusChange('finding', $finding->id, $findingOld, 'rejected');

            NotificationService::sendToUsers(
                $actionPlan->pic_user_id,
                'Tindak Lanjut Ditolak',
                'Tindak lanjut untuk temuan ' . $finding->finding_number . ' ditolak. Silakan cek catatan verifikasi.',
                route('findings.show', $finding->id),
                'danger'
            );
        }

        return redirect()->route('findings.show', $actionPlan->finding_id)
            ->with('success', 'Verifikasi selesai.');
    }

    public function uploadEvidence(Request $request, ActionPlan $actionPlan)
    {
        $user = auth()->user();
        // Bukti Perbaikan dikelola Kepala Divisi / PIC (matriks §8)
        $isKepalaDivisi = $user->role === 'kepala_divisi'
            && $user->division_id === $actionPlan->finding->auditPlan->division_id;
        if (auth()->id() !== $actionPlan->pic_user_id && !$isKepalaDivisi) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'evidence_file' => 'required|file|max:10240', // max 10MB
            'keterangan' => 'required|string|max:2000',
        ], [
            'keterangan.required' => 'Keterangan perbaikan wajib diisi.',
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
                'keterangan' => $request->input('keterangan'),
            ]);

            // Alur §15: upload bukti menandai pekerjaan berjalan (pending/rejected -> in_progress)
            if (in_array($actionPlan->status, ['pending', 'rejected'])) {
                $oldStatus = $actionPlan->status;
                $actionPlan->status = 'in_progress';
                $actionPlan->save();
                AuditLogHelper::logStatusChange('action_plan', $actionPlan->id, $oldStatus, 'in_progress');
            }

            // Alur §12: temuan yang ditolak kembali dikerjakan setelah perbaikan diupload
            $finding = $actionPlan->finding;
            if ($finding->status === 'rejected') {
                $finding->status = 'in_progress';
                $finding->save();
                AuditLogHelper::logStatusChange('finding', $finding->id, 'rejected', 'in_progress');
            }

            AuditLogHelper::logUpload('action_plan', $actionPlan->id, $filePath);

            return back()->with('success', 'Bukti tindak lanjut berhasil diupload.');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }
}