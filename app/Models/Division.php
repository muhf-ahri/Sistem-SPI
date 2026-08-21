<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'is_active'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function auditPlans()
    {
        return $this->hasMany(AuditPlan::class);
    }
}