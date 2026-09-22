<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFindingRequest extends FormRequest
{
    public function authorize()
    {
        // Temuan dikelola SPI/Auditor
        return auth()->user()->role === 'spi';
    }

    public function rules()
    {
        return [
            'category_id' => 'required|exists:finding_categories,id',
            'risk_category_id' => 'required|exists:risk_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'risk_description' => 'required|string',
            'criteria_explanation' => 'required|string',
            'recommendation' => 'required|string',
            'deadline' => 'required|date',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $validator->after(function ($validator) {
            // Batas waktu tindak lanjut harus berada di dalam rentang tanggal audit
            $finding = $this->route('finding');
            if (!$finding instanceof \App\Models\Finding) {
                return;
            }

            $plan = $finding->auditPlan;
            if (!$plan || !$plan->start_date || !$plan->end_date) {
                return;
            }

            if ($this->deadline) {
                $deadline = \Illuminate\Support\Carbon::parse($this->deadline)->startOfDay();
                if ($deadline->lt($plan->start_date->startOfDay())) {
                    $validator->errors()->add('deadline', 'Batas waktu tindak lanjut tidak boleh sebelum tanggal mulai audit (' . $plan->start_date->format('d M Y') . ').');
                } elseif ($deadline->gt($plan->end_date->endOfDay())) {
                    $validator->errors()->add('deadline', 'Batas waktu tindak lanjut tidak boleh melewati tanggal selesai audit (' . $plan->end_date->format('d M Y') . ').');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'category_id.required' => 'Kategori temuan harus dipilih.',
            'risk_category_id.required' => 'Tingkat risiko harus dipilih.',
            'title.required' => 'Judul temuan wajib diisi.',
            'description.required' => 'Deskripsi temuan wajib diisi.',
            'risk_description.required' => 'Deskripsi resiko wajib diisi.',
            'criteria_explanation.required' => 'Kriteria penjelasan wajib diisi.',
            'recommendation.required' => 'Rekomendasi perbaikan wajib diisi.',
            'deadline.required' => 'Batas waktu wajib diisi.',
        ];
    }
}