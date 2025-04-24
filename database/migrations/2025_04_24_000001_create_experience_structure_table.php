<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('experience_structure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained()->onDelete('cascade');
            $table->foreignId('structure_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['experience_id', 'structure_id']); // optional: to prevent duplicates
        });
    }

    public function down()
    {
        Schema::dropIfExists('experience_structure');
    }
};
