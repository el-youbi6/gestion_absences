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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('annee_scolaire_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('date');

            $table->time('heure_debut');

            $table->time('heure_fin');

            $table->boolean('is_justified')->default(false);

            $table->unique([
                'stagiaire_id',
                'module_id',
                'date',
                'heure_debut',
                'heure_fin',
            ], 'absences_unique_session');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
