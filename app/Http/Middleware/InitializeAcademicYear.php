<?php

namespace App\Http\Middleware;

use App\Services\AcademicYearService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeAcademicYear
{
    /**
     * Initialise l'année scolaire au démarrage de chaque requête
     */
    public function handle(Request $request, Closure $next): Response
    {
        AcademicYearService::initializeSession();

        return $next($request);
    }
}
