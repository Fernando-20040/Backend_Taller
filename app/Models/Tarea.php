<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Usuario; // <-- IMPORTANTE

class Tarea extends Model
{
    protected $fillable = [
        'titulo','descripcion','estado','fecha_vencimiento','user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
