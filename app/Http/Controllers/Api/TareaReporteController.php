<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarea;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TareaReporteController extends Controller
{
    /**
     * Exporta a Excel.
     * Por defecto: TODAS las tareas.
     * Filtro opcional: ?estado=pendiente|en_progreso|completada|todas
     */
    public function export(Request $request)
    {
        $estado  = $request->query('estado'); // null | 'todas' | 'pendiente' | 'en_progreso' | 'completada'
        $validos = ['pendiente', 'en_progreso', 'completada'];

        if ($estado && $estado !== 'todas' && !in_array($estado, $validos, true)) {
            return response()->json(['message' => 'Estado inválido'], 422);
        }

        if (!extension_loaded('zip') || !class_exists(\ZipArchive::class)) {
            return response()->json([
                'message' => 'La extensión ZIP de PHP no está habilitada. Activa extension=zip en php.ini y reinicia el servidor.'
            ], 500);
        }

        $query = Tarea::with('user:id,nombre');

        if ($estado && $estado !== 'todas') {
            $query->where('estado', $estado);
        }

        // Orden: primero tareas sin fecha, luego por fecha
        $query->orderByRaw('fecha_vencimiento IS NULL')->orderBy('fecha_vencimiento');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = ['ID', 'Título', 'Estado', 'Asignado a', 'Fecha de vencimiento', 'Creado'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        // Escribir filas (chunk por si hay muchas)
        $row = 2;
        $query->chunk(1000, function ($chunk) use (&$row, $sheet) {
            foreach ($chunk as $t) {
                $sheet->setCellValueByColumnAndRow(1, $row, $t->id);
                $sheet->setCellValueByColumnAndRow(2, $row, $t->titulo);
                $sheet->setCellValueByColumnAndRow(3, $row, $t->estado);
                $sheet->setCellValueByColumnAndRow(4, $row, optional($t->user)->nombre ?? '');
                $sheet->setCellValueByColumnAndRow(5, $row, $t->fecha_vencimiento);
                $sheet->setCellValueByColumnAndRow(6, $row, optional($t->created_at)?->format('Y-m-d H:i'));
                $row++;
            }
        });

        $filename = 'tareas_' . ($estado ?: 'todas') . '_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Exporta a CSV. Igual lógica de filtrado que Excel.
     */
    public function exportCsv(Request $request)
    {
        $estado  = $request->query('estado');
        $validos = ['pendiente', 'en_progreso', 'completada'];

        if ($estado && $estado !== 'todas' && !in_array($estado, $validos, true)) {
            return response()->json(['message' => 'Estado inválido'], 422);
        }

        $query = Tarea::with('user:id,nombre');

        if ($estado && $estado !== 'todas') {
            $query->where('estado', $estado);
        }

        $query->orderByRaw('fecha_vencimiento IS NULL')->orderBy('fecha_vencimiento');

        $filename = 'tareas_' . ($estado ?: 'todas') . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Encabezados
            fputcsv($handle, ['ID','Título','Estado','Asignado a','Fecha de vencimiento','Creado']);

            $query->chunk(1000, function ($chunk) use ($handle) {
                foreach ($chunk as $t) {
                    fputcsv($handle, [
                        $t->id,
                        $t->titulo,
                        $t->estado,
                        optional($t->user)->nombre ?? '',
                        $t->fecha_vencimiento,
                        optional($t->created_at)?->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
