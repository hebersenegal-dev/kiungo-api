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
      'Soci\u00e9t\u00e9 de production',
      'Salle de cin\u00e9ma',
      'Centre culturel',
      'Bureau d\u0027accueil des tournages',
      'Direction de la cin\u00e9matographie',
      'Fonds de soutien',
      'Diffuseur/cha\u00eene de t\u00e9l\u00e9vision/plateforme',
      'Association de professionnels',
      'Festival',
      'Institution publique',
      '\u00c9cole de cin\u00e9ma'
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
