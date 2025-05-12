<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'sector', 'country_id',
        'website', 'added_by', 'is_verified',
        'verified_at', 'verified_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function temporaryProjects()
    {
        return $this->hasMany(TemporaryProject::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class, 'temp_structure_id');
    }
}
