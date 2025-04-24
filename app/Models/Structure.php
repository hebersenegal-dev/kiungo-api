<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Structure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'website',
        'country_id',
    ];

    /**
     * Get the country where the structure is based.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the experiences associated with this structure.
     */
    public function experiences(): BelongsToMany
    {
        return $this->belongsToMany(Experience::class, 'experience_structure');
    }
}
