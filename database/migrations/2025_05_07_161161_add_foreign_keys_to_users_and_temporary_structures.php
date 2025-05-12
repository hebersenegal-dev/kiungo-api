<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUsersAndTemporaryStructures extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajout des clés étrangères dans la table users
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('temporary_structure_id')
                ->references('id')
                ->on('temporary_structures')
                ->onDelete('set null');

            $table->foreign('verified_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Ajout des clés étrangères dans la table temporary_structures
        Schema::table('temporary_structures', function (Blueprint $table) {
            $table->foreign('added_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression des clés étrangères
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['temporary_structure_id']);
            $table->dropForeign(['verified_by']);
        });

        Schema::table('temporary_structures', function (Blueprint $table) {
            $table->dropForeign(['added_by']);
        });
    }
}
