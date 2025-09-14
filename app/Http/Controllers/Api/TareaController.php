<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    // GET /api/tareas
    public function index()
    {
        // Devolvemos exactamente lo que tu frontend espera
        $tareas = Tarea::with(['user:id,nombre'])
            ->orderByDesc('id')
            ->get();

        return response()->json($tareas, 200);
    }

    // POST /api/tareas
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'            => ['required','string','max:255'],
            'descripcion'       => ['nullable','string'],
            'estado'            => ['required','in:pendiente,en_progreso,completada'],
            'fecha_vencimiento' => ['nullable','date'],
            'user_id'           => ['required','exists:usuarios,id'],
        ]);

        $tarea = Tarea::create($data)->load('user:id,nombre');

        return response()->json($tarea, 201);
    }
}