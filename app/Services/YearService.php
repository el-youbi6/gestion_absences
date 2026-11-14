<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\Session;

class YearService
{
    
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

    
    public static function getOrCreateCurrentYear()
    {
        $libelle = self::getCurrentYear();

        return AnneeScolaire::firstOrCreate(
            ['libelle' => $libelle],
            []
        );
    }

    
    public static function initializeSession(): void
    {
        if (!Session::has('annee_scolaire_id')) {
            $annee = self::getOrCreateCurrentYear();
            Session::put('annee_scolaire_id', $annee->id);
        }
    }

    
    public static function getSessionYear(): ?AnneeScolaire
    {
        $id = Session::get('annee_scolaire_id');
        
        if (!$id) {
            self::initializeSession();
            $id = Session::get('annee_scolaire_id');
        }

        return AnneeScolaire::find($id);
    }

    public static function getSessionYearId(): ?int
    {
        $annee = self::getSessionYear();
        return $annee?->id;
    }

    
    public static function setActiveYear(int $anneeId): void
    {
        $annee = AnneeScolaire::find($anneeId);
        
        if ($annee) {
            Session::put('annee_scolaire_id', $annee->id);
        }
    }


    public static function getAllYears()
    {
        return AnneeScolaire::orderByDesc('libelle')->get();
    }
}
