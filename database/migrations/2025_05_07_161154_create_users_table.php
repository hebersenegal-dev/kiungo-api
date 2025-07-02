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
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('company_name')->nullable();
      $table->string('email')->unique();
      $table->string('password');
      $table->enum('role', ['professional', 'structure', 'admin']);
      $table->enum('sector', [
        'societe_de_production',
        'salle_de_cinema',
        'centre_culturel',
        'bureau_accueil_tournages',
        'direction_cinematographie',
        'fonds_de_soutien',
        'diffuseur_chaine_tv_plateforme',
        'association_professionnels',
        'festival',
        'institution_publique',
        'ecole_de_cinema'
      ])->nullable();
      $table->enum('profession', [
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
      ])->nullable();
      $table->json('social_medias')->nullable();
      $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
      $table->string('website')->nullable();
      $table->text('bio')->nullable();
      $table->string('profile_picture')->nullable();
      $table->boolean('is_verified')->default(false);
      $table->timestamp('verified_at')->nullable();
      $table->unsignedBigInteger('verified_by')->nullable();
      $table->unsignedBigInteger('temporary_structure_id')->nullable();
      $table->timestamps();
      $table->rememberToken();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
