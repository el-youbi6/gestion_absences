<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\Session;

class YearService
{
    /**
     * Calcule l'année scolaire actuelle
     * Logique: si mois >= 9 => year/(year+1), sinon => (year-1)/year
     */
    public static function getCurrentYear(): string
    {
        $year = now()->year;
        $month = now()->month;

        if ($month >= 9) {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }

    /**
     * Récupère ou crée l'année scolaire actuelle
     */
    public static function getOrCreateCurrentYear()
    {
        $libelle = self::getCurrentYear();

        return AnneeScolaire::firstOrCreate(
            ['libelle' => $libelle],
            []
        );
    }

    /**
     * Initialise la session avec l'année scolaire actuelle
     */
    public static function initializeSession(): void
    {
        if (!Session::has('annee_scolaire_id')) {
            $annee = self::getOrCreateCurrentYear();
            Session::put('annee_scolaire_id', $annee->id);
        }
    }

    /**
     * Récupère l'année scolaire actuelle depuis la session
     */
    public static function getSessionYear(): ?AnneeScolaire
    {
        $id = Session::get('annee_scolaire_id');
        
        if (!$id) {
            self::initializeSession();
            $id = Session::get('annee_scolaire_id');
        }

        return AnneeScolaire::find($id);
    }

    /**
     * Récupère l'ID de l'année scolaire de session
     */
    public static function getSessionYearId(): ?int
    {
        $annee = self::getSessionYear();
        return $annee?->id;
    }

    /**
     * Change l'année scolaire active
     */
    public static function setActiveYear(int $anneeId): void
    {
        $annee = AnneeScolaire::find($anneeId);
        
        if ($annee) {
            Session::put('annee_scolaire_id', $annee->id);
        }
    }

    /**
     * Récupère toutes les années scolaires disponibles
     */
    public static function getAllYears()
    {
        return AnneeScolaire::orderByDesc('libelle')->get();
    }
}
