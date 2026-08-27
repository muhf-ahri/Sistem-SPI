<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActionPlanRequest extends FormRequest
{
    public function authorize()
    {
        // Action Plan dikelola Kepala Divisi
        return auth()->user()->role === 'kepala_divisi';
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'action' => 'required|string',
            'response' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,submitted,verified,rejected,completed',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul tindakan wajib diisi.',
            'action.required' => 'Rencana tindakan wajib diisi.',
            'status.required' => 'Status harus dipilih.',
        ];
    }
}