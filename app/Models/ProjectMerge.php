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
        'original_id',
        'official_id',
        'merged_by',
        'merged_at',
        'notes',
    ];

    protected $casts = [
        'merged_at' => 'datetime',
    ];

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
     * Notify the user when a temporary project is merged into an official one.
     */
     protected static function booted()
 {

     static::created(function (ProjectMerge $merge) {

       \Log::info("ProjectMerge created event fired", [
           'merge_id' => $merge->id,
           'original_id' => $merge->original_id,
       ]);

         // Get the temporary project
         $tempProject = TemporaryProject::find($merge->original_id);
         if (!$tempProject || !$tempProject->added_by) {
             return;
         }

         // Get the user who added the temporary project
         $user = User::find($tempProject->added_by);
         if (!$user) {
             return;
         }

         // Notify using mail + database + FCM
         $user->notify(new TempProjectVerified($tempProject, $merge->official_id, $user->locale));
     });
 }

}
