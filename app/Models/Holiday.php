<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name', 'type', 'note', 'year'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public const TYPES = [
        'national'      => 'Libur Nasional',
        'international' => 'Libur Internasional',
        'custom'        => 'Cuti / Libur Custom',
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
