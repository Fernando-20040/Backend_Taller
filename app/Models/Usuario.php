<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Tarea;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = ['nombre','email','password','rol'];

    protected $hidden = ['password'];

    // Hash automático del password al hacer $usuario->password = '...'
    protected $casts = [
        'password' => 'hashed',
    ];

    // Relación: un usuario tiene muchas tareas (FK: user_id)
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'user_id');
    }
}
