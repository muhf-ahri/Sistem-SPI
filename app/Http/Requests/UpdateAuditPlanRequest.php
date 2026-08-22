<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuditPlanRequest extends FormRequest
{
    public function authorize()
    {
        return in_array(auth()->user()->role, ['super_admin', 'spi']);
    }

    public function rules()
    {
        $auditPlan = $this->route('auditPlan');
        return [
            'division_id' => 'required|exists:divisions,id',
            'audit_type_id' => 'required|exists:audit_types,id',
            'audit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('audit_plans', 'audit_number')->ignore($auditPlan->id),
            ],
            'title' => 'required|string|max:255',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,scheduled,in_progress,completed,cancelled',
            'description' => 'nullable|string',
            'auditor_ids' => 'nullable|array',
            'auditor_ids.*' => 'exists:users,id,role,spi',
        ];
    }

    public function messages()
    {
        return [
            'division_id.required' => 'Divisi harus dipilih.',
            'audit_type_id.required' => 'Jenis pengawasan harus dipilih.',
            'audit_number.required' => 'Nomor pengawasan wajib diisi.',
            'audit_number.unique' => 'Nomor pengawasan sudah digunakan.',
            'title.required' => 'Judul pengawasan wajib diisi.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'status.required' => 'Status harus dipilih.',
            'auditor_ids.*.exists' => 'Auditor yang dipilih tidak valid.',
        ];
    }
}