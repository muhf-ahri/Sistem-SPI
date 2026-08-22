<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActionPlanRequest extends FormRequest
{
    public function authorize()
    {
        // Kepala divisi atau SPI bisa membuat action plan
        return in_array(auth()->user()->role, ['super_admin', 'spi', 'kepala_divisi']);
    }

    public function rules()
    {
        return [
            'finding_id' => 'required|exists:findings,id',
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
            'finding_id.required' => 'Temuan harus dipilih.',
            'pic_user_id.required' => 'Penanggung jawab harus dipilih.',
            'action.required' => 'Rencana tindakan wajib diisi.',
            'target_date.required' => 'Tanggal target wajib diisi.',
            'target_date.after' => 'Tanggal target harus setelah hari ini.',
            'status.required' => 'Status harus dipilih.',
        ];
    }
}