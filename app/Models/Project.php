<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'structure_id',
        'created_by',
        'website',
        'is_verified',
        'is_temp_created',
        'approved_by',
        'approved_at',
        'project_image'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_temp_created' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Automatically include the image URL in API responses
    protected $appends = ['project_image_url'];

    // Relationship to the user who owns the project
    public function structure()
    {
        return $this->belongsTo(User::class, 'structure_id');
    }

    public function professionals()
    {
        return $this->belongsToMany(User::class, 'experiences', 'project_id', 'user_id')
                    ->withPivot('role', 'title', 'start_date', 'end_date')
                    ->where('users.role', 'professional')
                    ->withTimestamps();
    }

    // Relationship to the creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    //  Relationship to the approver
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relationship to related experiences
    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }


    public function getProjectImageUrlAttribute()
    {
        return $this->project_image
            ? asset('storage/' . $this->project_image)
            : null;
    }
}
