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
    Schema::create('structure_merges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('original_id')->constrained('temporary_structures')->onDelete('cascade');
    $table->foreignId('official_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('merged_by')->constrained('users')->onDelete('cascade');
    $table->timestamp('merged_at');
    $table->text('notes')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structure_merges');
    }
};
