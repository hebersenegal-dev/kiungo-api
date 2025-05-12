<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemporaryProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'temp_structure_id',
        'website',
        'project_image',
        'added_by',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected $appends = ['project_image_url'];

    public function getProjectImageUrlAttribute()
    {
      return $this->project_image
          ? asset('storage/' . $this->project_image)
          : null;
    }



    public function structure()
{
    return $this->belongsTo(User::class, 'structure_id');
}

public function tempStructure()
{
    return $this->belongsTo(TemporaryStructure::class, 'temp_structure_id');
}


    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class, 'temp_project_id');
    }
}
