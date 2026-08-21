<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'finding_id', 'pic_user_id', 'action', 'target_date',
        'response', 'status'
    ];

    public function finding()
    {
        return $this->belongsTo(Finding::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function followUpEvidences()
    {
        return $this->hasMany(FollowUpEvidence::class);
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }
}