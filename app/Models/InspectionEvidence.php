<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionEvidence extends Model
{
    use HasFactory;

    protected $table = 'inspection_evidences';

    protected $fillable = [
        'inspection_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}