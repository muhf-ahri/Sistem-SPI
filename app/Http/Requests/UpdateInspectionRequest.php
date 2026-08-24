<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInspectionRequest extends FormRequest
{
    public function authorize()
    {
        // Pemeriksaan dikelola SPI/Auditor
        return auth()->user()->role === 'spi';
    }

    public function rules()
    {
        return [
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
            'inspection_date.required' => 'Tanggal pemeriksaan wajib diisi.',
            'result.in' => 'Hasil pemeriksaan tidak valid.',
        ];
    }
}