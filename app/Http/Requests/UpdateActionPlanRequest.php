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
            'pic_user_id' => 'required|exists:users,id',
            'action' => 'required|string',
            'target_date' => 'required|date|after:today',
            'response' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,submitted,verified,rejected,completed',
        ];
    }

    public function messages()
    {
        return [
            'pic_user_id.required' => 'Penanggung jawab harus dipilih.',
            'action.required' => 'Rencana tindakan wajib diisi.',
            'target_date.required' => 'Tanggal target wajib diisi.',
            'target_date.after' => 'Tanggal target harus setelah hari ini.',
            'status.required' => 'Status harus dipilih.',
        ];
    }
}