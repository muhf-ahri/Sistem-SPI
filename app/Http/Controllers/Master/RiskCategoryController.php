<?php

namespace App\Http\Controllers\Master;

use App\Models\RiskCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\AuditLogHelper;

class RiskCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(RiskCategory::class, 'riskCategory');
    }

    public function index()
    {
        $riskCategories = RiskCategory::orderBy('level')->orderBy('name')->paginate(10);
        return view('master.risk-categories.index', compact('riskCategories'));
    }

    public function create()
    {
        return view('master.risk-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $riskCategory = RiskCategory::create($request->all());
        AuditLogHelper::log('create', 'risk_category', $riskCategory->id, null, $riskCategory->toArray());
        return redirect()->route('master.risk-categories.index')->with('success', 'Kategori risiko berhasil ditambahkan.');
    }

    public function show(RiskCategory $riskCategory)
    {
        return view('master.risk-categories.show', compact('riskCategory'));
    }

    public function edit(RiskCategory $riskCategory)
    {
        return view('master.risk-categories.edit', compact('riskCategory'));
    }

    public function update(Request $request, RiskCategory $riskCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $old = $riskCategory->toArray();
        $riskCategory->update($request->all());
        AuditLogHelper::log('update', 'risk_category', $riskCategory->id, $old, $riskCategory->toArray());
        return redirect()->route('master.risk-categories.index')->with('success', 'Kategori risiko berhasil diperbarui.');
    }

    public function destroy(RiskCategory $riskCategory)
    {
        // Cek apakah ada relasi
        if ($riskCategory->findings()->count() > 0) {
            return back()->with('error', 'Kategori risiko tidak bisa dihapus karena memiliki data temuan.');
        }
        $riskCategory->delete();
        AuditLogHelper::log('delete', 'risk_category', $riskCategory->id, $riskCategory->toArray(), null);
        return redirect()->route('master.risk-categories.index')->with('success', 'Kategori risiko berhasil dihapus.');
    }
}