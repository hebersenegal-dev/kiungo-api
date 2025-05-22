<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
      'Réalisateur(trice)',
      'Producteur(trice)',
      'Assistant(e) réalisateur(trice)',
      'Scripte',
      'Chargé(e) de production',
      'Directeur(trice) de la photographie',
      'Chef opérateur(trice)',
      'Cadreur(euse)',
      'Caméraman',
      'Assistant(e) opérateur(trice)',
      '1er assistant caméra',
      'Étalonneur(euse)',
      'Ingénieur(e) du son',
      'Chef opérateur(trice) son',
      'Perchiste',
      'Monteur(euse) son',
      'Mixeur(euse)',
      'Designer sonore',
      'Monteur(euse) image',
      'Monteur(euse) effets spéciaux',
      'Motion designer',
      'Scénariste',
      'Dialoguiste',
      'Documentariste',
      'Animateur(trice) 2D',
      'Animateur(trice) 3D',
      'Storyboarder',
      'Concept artist',
      'Journaliste reporter d\'images (JRI)',
      'Régisseur(se) général(e)',
      'Technicien(ne) broadcasting',
      'Vidéaste',
      'Technicien(ne) plateau',
      'Opérateur(trice) lumière',
      'Créateur(trice) de contenu vidéo',
      'Spécialiste en réalité virtuelle',
      'Spécialiste en réalité augmentée',
      'Responsable de chaîne',
      'Community manager vidéo'
    ];

    // Cast the role enum for better type handling
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
