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
  $table->string('role');
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
