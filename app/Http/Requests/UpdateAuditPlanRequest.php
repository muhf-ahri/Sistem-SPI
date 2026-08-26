<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuditPlanRequest extends FormRequest
{
    public function authorize()
    {
        // Rencana pengawasan dikelola SPI/Auditor (Super Admin hanya melihat)
        return auth()->user()->role === 'spi';
    }

    public function rules()
    {
        // Parameter route resource bernama audit_plan (snake_case)
        $auditPlan = $this->route('audit_plan');
        return [
            'division_id' => 'required|exists:divisions,id',
            'audit_type_id' => 'required|exists:audit_types,id',
            'audit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('audit_plans', 'audit_number')->ignore($auditPlan?->id),
            ],
            'title' => 'required|string|max:255',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
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
            'auditor_ids.*.exists' => 'Auditor yang dipilih tidak valid.',
        ];
    }
}