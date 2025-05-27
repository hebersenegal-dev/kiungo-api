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
