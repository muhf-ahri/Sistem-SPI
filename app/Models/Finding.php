<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finding extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id', 'inspection_id', 'category_id', 'risk_category_id',
        'created_by', 'finding_number', 'title', 'description',
        'risk_description', 'criteria_explanation',
        'recommendation', 'deadline', 'status'
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function category()
    {
        return $this->belongsTo(FindingCategory::class, 'category_id');
    }

    public function riskCategory()
    {
        return $this->belongsTo(RiskCategory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actionPlans()
    {
        return $this->hasMany(ActionPlan::class);
    }
}