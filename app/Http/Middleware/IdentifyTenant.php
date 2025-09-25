<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Resolver subdominio o fallback
        $subdomain = $this->resolveSubdomain($request);
        if (!$subdomain) {
            return response()->json(['message' => 'Tenant no especificado'], 400);
        }

        // 2) Buscar tenant en landlord
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        if (!$tenant) {
            return response()->json(['message' => 'Tenant desconocido'], 404);
        }

        // 3) Crear configuración 'tenant'
        $base = Config::get('database.connections.mysql'); // toma tu conexión base
        $configTenant = array_merge($base, [
            'host'     => $tenant->db_host,
            'port'     => $tenant->db_port,
            'database' => $tenant->db_database,
            'username' => $tenant->db_username,
            'password' => $tenant->db_password,
        ]);

        
        // if ($tenant->db_schema) {
        //     $configTenant['schema'] = $tenant->db_schema;
        //     $configTenant['search_path'] = $tenant->db_schema;
        // }

        // 4) Establecer 'tenant' como conexión default para este request
        Config::set('database.connections.tenant', $configTenant);
        DB::purge('tenant');
        Config::set('database.default', 'tenant');
        DB::reconnect('tenant');

        app()->instance('tenant', $tenant); 

        return $next($request);
    }

    private function resolveSubdomain(Request $request): ?string
    {
        // A) subdominio:
        $host = $request->getHost();
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            return $parts[0];
        }

        // B) Header X-Tenant
        $h = $request->header('X-Tenant');
        if ($h) return $h;

        
        $q = $request->query('tenant');
        if ($q) return $q;

        return null;
    }
}
