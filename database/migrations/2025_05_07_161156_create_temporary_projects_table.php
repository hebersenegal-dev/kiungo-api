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
      Schema::create('temporary_projects', function (Blueprint $table) {
  $table->id();
  $table->string('title');
  $table->text('description');
  $table->date('start_date');
  $table->date('end_date');
  $table->foreignId('temp_structure_id')->nullable()->constrained('temporary_structures')->onDelete('cascade');
  $table->foreignId('structure_id')->nullable()->constrained('users')->onDelete('cascade');
  $table->string('website')->nullable();
  $table->string('project_image')->nullable();
  $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
  $table->boolean('is_verified')->default(false);
  $table->timestamp('verified_at')->nullable();
  $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
  $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_projects');
    }
};
