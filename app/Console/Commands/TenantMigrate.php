<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate {--fresh} {--seed}';
    protected $description = 'Ejecuta migraciones en todas las BD de tenants';

    public function handle()
    {
        $tenants = Tenant::all();
        if ($tenants->isEmpty()) {
            $this->warn('No hay tenants registrados.');
            return self::SUCCESS;
        }

        foreach ($tenants as $t) {
            $this->line("➤ Migrando tenant: {$t->subdomain}");

            $base = Config::get('database.connections.mysql');
            $cfg = array_merge($base, [
                'host'     => $t->db_host,
                'port'     => $t->db_port,
                'database' => $t->db_database,
                'username' => $t->db_username,
                'password' => $t->db_password,
            ]);

            Config::set('database.connections.tenant', $cfg);
            DB::purge('tenant');
            Config::set('database.default', 'tenant');
            DB::reconnect('tenant');

            $params = ['--force' => true];
            if ($this->option('fresh')) {
                $this->call('migrate:fresh', $params);
            } else {
                $this->call('migrate', $params);
            }
            if ($this->option('seed')) {
                $this->call('db:seed', $params);
            }
        }

        $this->info('✔ Migraciones de tenants completadas.');
        return self::SUCCESS;
    }
}
