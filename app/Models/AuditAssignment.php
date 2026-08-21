<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['audit_plan_id', 'user_id', 'role', 'assigned_at'];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}