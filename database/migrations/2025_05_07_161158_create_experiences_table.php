<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('structure_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('temp_structure_id')->nullable()->constrained('temporary_structures')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('temp_project_id')->nullable()->constrained('temporary_projects')->nullOnDelete();
            $table->string('title');
            $table->enum('role', [
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
            ]);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
