<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GlobalImport;
use Illuminate\Support\Facades\Session;
use Exception;

class ImportService
{
    /**
     * Importe un fichier Excel avec la logique centralisée d'année scolaire
     */
    public static function importGlobalFile(string $filePath): array
    {
        // Initialiser l'année scolaire
        AcademicYearService::initializeSession();

        $anneeId = AcademicYearService::getSessionAcademicYearId();
        
        if (!$anneeId) {
            throw new Exception('Aucune année scolaire n\'a pu être déterminée');
        }

        try {
            // Importer le fichier
            Excel::import(new GlobalImport(), $filePath);

            return [
                'success' => true,
                'message' => 'Importation complétée avec succès pour l\'année ' . 
                    AcademicYearService::getSessionAcademicYear()?->libelle,
                'annee_id' => $anneeId
            ];
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'importation: ' . $e->getMessage());
        }
    }
}
