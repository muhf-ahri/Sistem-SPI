<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActionPlanRequest extends FormRequest
{
    public function authorize()
    {
        // Action Plan dibuat oleh Kepala Divisi
        return auth()->user()->role === 'kepala_divisi';
    }

    public function rules()
    {
        return [
            'finding_id' => 'required|exists:findings,id',
            'title' => 'required|string|max:255',
            'action' => 'required|string',
            'response' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,submitted,verified,rejected,completed',
        ];
    }

    public function messages()
    {
        return [
            'finding_id.required' => 'Temuan harus dipilih.',
            'title.required' => 'Judul tindakan wajib diisi.',
            'action.required' => 'Rencana tindakan wajib diisi.',
            'status.required' => 'Status harus dipilih.',
        ];
    }
}