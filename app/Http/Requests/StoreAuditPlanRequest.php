<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuditPlanRequest extends FormRequest
{
    public function authorize()
    {
        // Rencana pengawasan dibuat oleh SPI/Auditor (Super Admin hanya melihat)
        return auth()->user()->role === 'spi';
    }

    public function rules()
    {
        return [
            'division_id' => 'required|exists:divisions,id',
            'audit_type_id' => 'required|exists:audit_types,id',
            'audit_number' => 'required|string|max:50|unique:audit_plans,audit_number',
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