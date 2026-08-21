<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id', 'auditor_id', 'inspection_date',
        'summary', 'notes', 'result'
    ];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function evidences()
    {
        return $this->hasMany(InspectionEvidence::class);
    }

    public function findings()
    {
        return $this->hasMany(Finding::class);
    }
}