<?php

namespace App\Http\Middleware;

use App\Services\YearService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeYear
{
    /**
     * Initialise l'année scolaire au démarrage de chaque requête
     */
    public function handle(Request $request, Closure $next): Response
    {
        YearService::initializeSession();

        return $next($request);
    }
}
