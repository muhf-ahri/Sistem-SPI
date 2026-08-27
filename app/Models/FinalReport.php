<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id', 'report_number', 'title', 'file_path',
        'file_name', 'file_type', 'file_size', 'description', 'created_by'
    ];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}