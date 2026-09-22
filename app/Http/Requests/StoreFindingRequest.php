<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFindingRequest extends FormRequest
{
    public function authorize()
    {
        // Temuan dibuat oleh SPI/Auditor
        return auth()->user()->role === 'spi';
    }

    public function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $validator->after(function ($validator) {
            // Batas waktu tindak lanjut harus berada di dalam rentang tanggal audit
            if (!$this->audit_plan_id) {
                return;
            }

            if ($this->deadline) {
                $plan = \App\Models\AuditPlan::find($this->audit_plan_id);
                if ($plan && $plan->start_date && $plan->end_date) {
                    $deadline = \Illuminate\Support\Carbon::parse($this->deadline)->startOfDay();
                    if ($deadline->lt($plan->start_date->startOfDay())) {
                        $validator->errors()->add('deadline', 'Batas waktu tindak lanjut tidak boleh sebelum tanggal mulai audit (' . $plan->start_date->format('d M Y') . ').');
                    } elseif ($deadline->gt($plan->end_date->endOfDay())) {
                        $validator->errors()->add('deadline', 'Batas waktu tindak lanjut tidak boleh melewati tanggal selesai audit (' . $plan->end_date->format('d M Y') . ').');
                    }
                }
            }

            if (!$this->inspection_id) {
                return;
            }

            $inspection = \App\Models\Inspection::withCount('findings')->find($this->inspection_id);
            if (!$inspection) {
                return;
            }

            if ($inspection->result === 'satisfactory') {
                $validator->errors()->add('inspection_id', 'Pemeriksaan dengan hasil Satisfactory tidak dapat dijadikan dasar temuan.');
            }

            if ($inspection->findings_count > 0) {
                $validator->errors()->add('inspection_id', 'Pemeriksaan ini sudah dijadikan dasar untuk temuan lain. Satu pemeriksaan hanya dapat menghasilkan satu temuan.');
            }
        });
    }

    public function rules()
    {
        return [
            'audit_plan_id' => 'required|exists:audit_plans,id',
            'inspection_id' => 'nullable|exists:inspections,id',
            'category_id' => 'required|exists:finding_categories,id',
            'risk_category_id' => 'required|exists:risk_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'risk_description' => 'required|string',
            'criteria_explanation' => 'required|string',
            'recommendation' => 'required|string',
            'deadline' => 'required|date',
            'status' => 'required|in:open,in_progress,waiting_verification,closed,rejected',
        ];
    }

    public function messages()
    {
        return [
            'audit_plan_id.required' => 'Audit harus dipilih.',
            'category_id.required' => 'Kategori temuan harus dipilih.',
            'risk_category_id.required' => 'Tingkat risiko harus dipilih.',
            'title.required' => 'Judul temuan wajib diisi.',
            'description.required' => 'Deskripsi temuan wajib diisi.',
            'risk_description.required' => 'Deskripsi resiko wajib diisi.',
            'criteria_explanation.required' => 'Kriteria penjelasan wajib diisi.',
            'recommendation.required' => 'Rekomendasi perbaikan wajib diisi.',
            'deadline.required' => 'Batas waktu wajib diisi.',
            'status.required' => 'Status harus dipilih.',
        ];
    }
}