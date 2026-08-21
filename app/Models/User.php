<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function createdAuditPlans()
    {
        return $this->hasMany(AuditPlan::class, 'created_by');
    }

    public function auditAssignments()
    {
        return $this->hasMany(AuditAssignment::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'auditor_id');
    }

    public function uploadedInspectionEvidences()
    {
        return $this->hasMany(InspectionEvidence::class, 'uploaded_by');
    }

    public function createdFindings()
    {
        return $this->hasMany(Finding::class, 'created_by');
    }

    public function picActionPlans()
    {
        return $this->hasMany(ActionPlan::class, 'pic_user_id');
    }

    public function uploadedFollowUpEvidences()
    {
        return $this->hasMany(FollowUpEvidence::class, 'uploaded_by');
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class, 'verifier_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
