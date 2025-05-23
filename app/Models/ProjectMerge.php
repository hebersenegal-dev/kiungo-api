<?php

namespace App\Models;

use App\Models\TemporaryProject;
use App\Models\Project;
use App\Models\User;
use App\Notifications\TempProjectVerified;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMerge extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'original_id', 'official_id', 'merged_by', 'merged_at', 'notes'
    ];

    protected $casts = ['merged_at' => 'datetime'];

    public function original()
    {
        return $this->belongsTo(TemporaryProject::class, 'original_id');
    }

    public function official()
    {
        return $this->belongsTo(Project::class, 'official_id');
    }

    public function mergedBy()
    {
        return $this->belongsTo(User::class, 'merged_by');
    }

    /**
     * Booted model hook to notify user when project is merged.
     */
     protected static function booted()
 {
     static::created(function ($merge) {
         $tempProject = \App\Models\TemporaryProject::find($merge->original_id);

         if ($tempProject && $tempProject->added_by) {
             $user = \App\Models\User::find($tempProject->added_by);

             if ($user) {
                 $user->notify(new \App\Notifications\TempProjectVerified($tempProject, $merge->official_id));
             }
         }
     });
 }

}
