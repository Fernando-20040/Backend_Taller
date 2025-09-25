<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('landlord')->table('tenants')->insert([
            [
                'name'        => 'Empresa 1',
                'subdomain'   => 'empresa1',
                'db_host'     => env('DB_HOST', '127.0.0.1'),
                'db_port'     => env('DB_PORT', '3306'),
                'db_database' => 'empresa1_db',
                'db_username' => env('DB_USERNAME', 'root'),
                'db_password' => env('DB_PASSWORD', ''),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Empresa 2',
                'subdomain'   => 'empresa2',
                'db_host'     => env('DB_HOST', '127.0.0.1'),
                'db_port'     => env('DB_PORT', '3306'),
                'db_database' => 'empresa2_db',
                'db_username' => env('DB_USERNAME', 'root'),
                'db_password' => env('DB_PASSWORD', ''),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
