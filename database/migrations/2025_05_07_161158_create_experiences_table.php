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
              'R\u00e9alisateur(trice)',
    'Producteur(trice)',
    'Assistant(e) r\u00e9alisateur(trice)',
    'Scripte',
    'Charg\u00e9(e) de production',
    'Directeur(trice) de la photographie',
    'Chef op\u00e9rateur(trice)',
    'Cadreur(euse)',
    'Cam\u00e9raman',
    'Assistant(e) op\u00e9rateur(trice)',
    '1er assistant cam\u00e9ra',
    '\u00c9talonneur(euse)',
    'Ing\u00e9nieur(e) du son',
    'Chef op\u00e9rateur(trice) son',
    'Perchiste',
    'Monteur(euse) son',
    'Mixeur(euse)',
    'Designer sonore',
    'Monteur(euse) image',
    'Monteur(euse) effets sp\u00e9ciaux',
    'Motion designer',
    'Sc\u00e9nariste',
    'Dialoguiste',
    'Documentariste',
    'Animateur(trice) 2D',
    'Animateur(trice) 3D',
    'Storyboarder',
    'Concept artist',
    'Journaliste reporter d\u0027images (JRI)',
    'R\u00e9gisseur(se) g\u00e9n\u00e9ral(e)',
    'Technicien(ne) broadcasting',
    'Vidéaste',
    'Technicien(ne) plateau',
    'Op\u00e9rateur(trice) lumi\u00e8re',
    'Cr\u00e9ateur(trice) de contenu vid\u00e9o',
    'Sp\u00e9cialiste en r\u00e9alit\u00e9 virtuelle',
    'Sp\u00e9cialiste en r\u00e9alit\u00e9 augment\u00e9e',
    'Responsable de cha\u00eene',
    'Community manager vid\u00e9o'
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
