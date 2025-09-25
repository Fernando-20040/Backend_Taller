<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
   
    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'subdomain',
        'db_host', 'db_port', 'db_database', 'db_username', 'db_password',
        'db_schema', 
    ];
}
