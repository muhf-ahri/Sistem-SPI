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

    public function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $validator->after(function ($validator) {
            $inspection = $this->route('inspection');
            if (!$inspection || !$inspection->auditPlan) {
                return;
            }

            $plan = $inspection->auditPlan;
            $date = $this->inspection_date;
            if (!$date) {
                return;
            }

            $start = $plan->start_date ? \Illuminate\Support\Carbon::parse($plan->start_date) : null;
            $end = $plan->end_date ? \Illuminate\Support\Carbon::parse($plan->end_date) : null;

            $date = \Illuminate\Support\Carbon::parse($date);

            if ($start && $date < $start) {
                $validator->errors()->add('inspection_date', 'Tanggal pemeriksaan tidak boleh sebelum tanggal mulai Audit (' . $start->toDateString() . ').');
            }

            if ($end && $date > $end) {
                $validator->errors()->add('inspection_date', 'Tanggal pemeriksaan tidak boleh melebihi tanggal selesai Audit (' . $end->toDateString() . ').');
            }
        });
    }

    public function messages()
    {
        return [
            'inspection_date.required' => 'Tanggal pemeriksaan wajib diisi.',
            'result.in' => 'Hasil pemeriksaan tidak valid.',
        ];
    }
}