<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'phone_number',
        'profile_picture',
        'role',
        'location_id',
        'bio',
        'facebook_link',
        'twitter_link',
        'linkedin_link',
        'youtube_link',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the country associated with the user.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'location_id');
    }

    /**
     * Experiences created/owned by the user.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }
}
