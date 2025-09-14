<?php

namespace App\Exports;

use App\Models\Tarea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TareasReporteExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected string $estado = 'pendiente') {}

    public function collection()
    {
        return Tarea::with('user:id,nombre')
            ->where('estado', $this->estado)
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    public function headings(): array
    {
        return ['ID','Título','Estado','Asignado a','Fecha de vencimiento','Creado'];
    }

    public function map($t): array
    {
        return [
            $t->id,
            $t->titulo,
            $t->estado,
            $t->user?->nombre ?? '',
            $t->fecha_vencimiento,
            optional($t->created_at)->format('Y-m-d H:i'),
        ];
    }
}
