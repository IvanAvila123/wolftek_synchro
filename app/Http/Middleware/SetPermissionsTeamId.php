<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            setPermissionsTeamId($tenant->id);
        }

        return $next($request);
    }
}