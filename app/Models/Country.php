<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_code',
    ];

    /**
     * Get the users associated with this country.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'location_id');
    }

    /**
     * Get the structures based in this country.
     */
    public function structures(): HasMany
    {
        return $this->hasMany(Structure::class);
    }
}
