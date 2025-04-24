<?php
// app/Models/Experience.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'role',
        'project_link',
        'start_year',
        'end_year',
    ];

    /**
     * The user who owns the experience.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Other professionals (users) involved in this experience.
     */
    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'experience_user');
    }

    /**
     * Structures associated with this experience.
     */
    public function structures(): BelongsToMany
    {
        return $this->belongsToMany(Structure::class, 'experience_structure');
    }
}
