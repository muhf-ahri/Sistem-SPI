<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id', 'audit_type_id', 'created_by', 'audit_number',
        'title', 'start_date', 'end_date', 'status', 'description'
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function auditType()
    {
        return $this->belongsTo(AuditType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(AuditAssignment::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function findings()
    {
        return $this->hasMany(Finding::class);
    }

    public function finalReports()
    {
        return $this->hasMany(FinalReport::class);
    }
}