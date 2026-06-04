<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formateur_groupe', function (Blueprint $table) {
            $table->foreignId('annee_scolaire_id')
                ->nullable()
                ->after('groupe_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['formateur_id', 'groupe_id', 'annee_scolaire_id'], 'fg_formateur_groupe_annee_unique');
        });

        Schema::table('formateur_module', function (Blueprint $table) {
            $table->foreignId('annee_scolaire_id')
                ->nullable()
                ->after('module_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['formateur_id', 'module_id', 'annee_scolaire_id'], 'fm_formateur_module_annee_unique');
        });
    }

    public function down(): void
    {
        Schema::table('formateur_groupe', function (Blueprint $table) {
            $table->dropUnique('fg_formateur_groupe_annee_unique');
            $table->dropConstrainedForeignId('annee_scolaire_id');
        });

        Schema::table('formateur_module', function (Blueprint $table) {
            $table->dropUnique('fm_formateur_module_annee_unique');
            $table->dropConstrainedForeignId('annee_scolaire_id');
        });
    }
};
