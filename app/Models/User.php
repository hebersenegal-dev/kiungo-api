<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'password',
        'role',
        'sector',
        'country_id',
        'website',
        'bio',
        'is_verified',
        'verified_at',
        'verified_by',
        'temporary_structure_id',
        'profile_picture',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];


    protected $appends = ['profile_picture_url'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function temporaryStructure()
    {
        return $this->belongsTo(TemporaryStructure::class, 'temporary_structure_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function approvedProjects()
    {
        return $this->hasMany(Project::class, 'approved_by');
    }

    public function addedTemporaryStructures()
    {
        return $this->hasMany(TemporaryStructure::class, 'added_by');
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }


    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : null;
    }


}
