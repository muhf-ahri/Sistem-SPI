<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_plan_id', 'verifier_id', 'result', 'notes', 'verified_at'
    ];

    public function actionPlan()
    {
        return $this->belongsTo(ActionPlan::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function user()
    {
        return $this->verifier();
    }
}