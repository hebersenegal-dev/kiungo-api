<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StructureMerge extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'original_id', 'official_id', 'merged_by', 'merged_at', 'notes'
    ];

    protected $casts = ['merged_at' => 'datetime'];

    public function original()
    {
        return $this->belongsTo(TemporaryStructure::class, 'original_id');
    }

    public function official()
    {
        return $this->belongsTo(User::class, 'official_id');
    }

    public function mergedBy()
    {
        return $this->belongsTo(User::class, 'merged_by');
    }
}
