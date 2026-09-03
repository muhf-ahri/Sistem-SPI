<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id', 'audit_type_id', 'created_by', 'audit_number',
        'title', 'start_date', 'end_date', 'working_days', 'status', 'description'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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

    // Apakah user ini auditor yang ditugaskan untuk Audit ini?
    // Jika belum ada penugasan sama sekali, akses tetap dibolehkan (backward-compatible)
    // agar Audit lama yang tidak punya data assignment tidak terkunci.
    public function assignedTo(User $user): bool
    {
        $hasAssignment = $this->assignments()->exists();
        if (!$hasAssignment) {
            return true;
        }
        return $this->assignments()
            ->where('user_id', $user->id)
            ->exists();
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