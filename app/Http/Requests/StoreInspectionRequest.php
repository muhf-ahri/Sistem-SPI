<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspectionRequest extends FormRequest
{
    public function authorize()
    {
        return in_array(auth()->user()->role, ['super_admin', 'spi']);
    }

    public function rules()
    {
        return [
            'audit_plan_id' => 'required|exists:audit_plans,id',
            'auditor_id' => 'nullable|exists:users,id,role,spi',
            'inspection_date' => 'required|date',
            'summary' => 'nullable|string',
            'notes' => 'nullable|string',
            'result' => 'nullable|in:satisfactory,needs_improvement,non_conformity',
        ];
    }

    public function messages()
    {
        return [
            'audit_plan_id.required' => 'Pengawasan harus dipilih.',
            'inspection_date.required' => 'Tanggal pemeriksaan wajib diisi.',
            'result.in' => 'Hasil pemeriksaan tidak valid.',
        ];
    }
}