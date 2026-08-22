<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFindingRequest extends FormRequest
{
    public function authorize()
    {
        return in_array(auth()->user()->role, ['super_admin', 'spi']);
    }

    public function rules()
    {
        return [
            'audit_plan_id' => 'required|exists:audit_plans,id',
            'inspection_id' => 'nullable|exists:inspections,id',
            'category_id' => 'required|exists:finding_categories,id',
            'risk_category_id' => 'required|exists:risk_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'recommendation' => 'nullable|string',
            'deadline' => 'required|date|after:today',
            'status' => 'required|in:open,in_progress,waiting_verification,closed,rejected',
        ];
    }

    public function messages()
    {
        return [
            'audit_plan_id.required' => 'Pengawasan harus dipilih.',
            'category_id.required' => 'Kategori temuan harus dipilih.',
            'risk_category_id.required' => 'Tingkat risiko harus dipilih.',
            'title.required' => 'Judul temuan wajib diisi.',
            'description.required' => 'Deskripsi temuan wajib diisi.',
            'deadline.required' => 'Batas waktu wajib diisi.',
            'deadline.after' => 'Batas waktu harus setelah hari ini.',
            'status.required' => 'Status harus dipilih.',
        ];
    }
}