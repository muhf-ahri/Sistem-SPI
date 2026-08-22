<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpEvidence extends Model
{
    use HasFactory;

    protected $table = 'follow_up_evidences';

    protected $fillable = [
        'action_plan_id', 'uploaded_by', 'file_name',
        'file_path', 'file_type', 'file_size'
    ];

    public function actionPlan()
    {
        return $this->belongsTo(ActionPlan::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}