<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'structure_id', 'temp_structure_id', 'project_id',
        'temp_project_id', 'title', 'role', 'start_date', 'end_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function structure()
    {
        return $this->belongsTo(User::class, 'structure_id');
    }

    public function tempStructure()
    {
        return $this->belongsTo(TemporaryStructure::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tempProject()
    {
        return $this->belongsTo(TemporaryProject::class);
    }
}
