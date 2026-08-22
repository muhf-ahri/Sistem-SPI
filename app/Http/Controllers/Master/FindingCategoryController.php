<?php

namespace App\Http\Controllers\Master;

use App\Models\FindingCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\AuditLogHelper;

class FindingCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FindingCategory::class, 'finding_category');
    }

    public function index()
    {
        $categories = FindingCategory::orderBy('name')->paginate(10);
        return view('master.finding-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('master.finding-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category = FindingCategory::create($request->all());
        AuditLogHelper::log('create', 'finding_category', $category->id, null, $category->toArray());
        return redirect()->route('master.finding-categories.index')->with('success', 'Kategori temuan berhasil ditambahkan.');
    }

    public function show(FindingCategory $findingCategory)
    {
        return view('master.finding-categories.show', compact('findingCategory'));
    }

    public function edit(FindingCategory $findingCategory)
    {
        return view('master.finding-categories.edit', compact('findingCategory'));
    }

    public function update(Request $request, FindingCategory $findingCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $old = $findingCategory->toArray();
        $findingCategory->update($request->all());
        AuditLogHelper::log('update', 'finding_category', $findingCategory->id, $old, $findingCategory->toArray());
        return redirect()->route('master.finding-categories.index')->with('success', 'Kategori temuan berhasil diperbarui.');
    }

    public function destroy(FindingCategory $findingCategory)
    {
        // Cek apakah ada relasi
        if ($findingCategory->findings()->count() > 0) {
            return back()->with('error', 'Kategori temuan tidak bisa dihapus karena memiliki data temuan.');
        }
        $findingCategory->delete();
        AuditLogHelper::log('delete', 'finding_category', $findingCategory->id, $findingCategory->toArray(), null);
        return redirect()->route('master.finding-categories.index')->with('success', 'Kategori temuan berhasil dihapus.');
    }
}