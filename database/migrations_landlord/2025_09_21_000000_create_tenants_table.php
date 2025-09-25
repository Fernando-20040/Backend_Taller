<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection($this->connection)->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('subdomain', 100)->unique();

            // Datos conexión (MariaDB/MySQL por base de datos)
            $table->string('db_host')->default(env('DB_HOST', '127.0.0.1'));
            $table->string('db_port')->default(env('DB_PORT', '3306'));
            $table->string('db_database'); 
            $table->string('db_username')->default(env('DB_USERNAME', 'root'));
            $table->string('db_password')->default(env('DB_PASSWORD', ''));

            
            $table->string('db_schema')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenants');
    }
};
