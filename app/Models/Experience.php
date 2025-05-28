<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\NewProjectForStructure;
use Illuminate\Support\Facades\Notification;

class Experience extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'structure_id',
        'temp_structure_id',
        'project_id',
        'temp_project_id',
        'title',
        'role',
        'start_date',
        'end_date',
    ];

    // Consider defining the enum values as a constant
    public const ROLES = [
      'realisateur',
'producteur',
'assistant_realisateur',
'scripte',
'charge_production',
'directeur_photographie',
'chef_operateur',
'cadreur',
'cameraman',
'assistant_operateur',
'premier_assistant_camera',
'etalonneur',
'ingenieur_son',
'chef_operateur_son',
'perchiste',
'monteur_son',
'mixeur',
'designer_sonore',
'monteur_image',
'monteur_effets_speciaux',
'motion_designer',
'scenariste',
'dialoguiste',
'documentariste',
'animateur_2d',
'animateur_3d',
'storyboarder',
'concept_artist',
'journaliste_reporter_images',
'regisseur_general',
'technicien_broadcasting',
'videaste',
'technicien_plateau',
'operateur_lumiere',
'createur_contenu_video',
'specialiste_realite_virtuelle',
'specialiste_realite_augmentee',
'responsable_chaine',
'community_manager_video'
    ];

    // Cast the role enum for better type handling
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted()
  {
      static::created(function ($experience) {
          if ($experience->structure_id && $experience->structure) {
              Notification::send($experience->structure, new NewProjectForStructure($experience));
          }
      });
  }



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
        return $this->belongsTo(TemporaryStructure::class, 'temp_structure_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tempProject()
    {
        return $this->belongsTo(TemporaryProject::class, 'temp_project_id');
    }
}
