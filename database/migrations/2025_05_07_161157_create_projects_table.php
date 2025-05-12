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
      Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->date('start_date');
    $table->date('end_date');
    $table->foreignId('structure_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->string('website')->nullable();
        $table->string('project_image')->nullable(); 
    $table->boolean('is_verified')->default(false);
    $table->boolean('is_temp_created')->default(false);
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
