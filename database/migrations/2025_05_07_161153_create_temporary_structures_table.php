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
      Schema::create('temporary_structures', function (Blueprint $table) {
    $table->id();
    $table->string('company_name');
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
    $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
    $table->string('website')->nullable();
    $table->unsignedBigInteger('added_by'); // FK deferred
    $table->boolean('is_verified')->default(false);
    $table->timestamp('verified_at')->nullable();
    $table->unsignedBigInteger('verified_by')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_structures');
    }
};
