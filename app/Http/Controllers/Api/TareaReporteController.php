<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarea;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TareaReporteController extends Controller
{
    // GET /api/tareas/reporte?estado=pendiente
    public function export(Request $request)
    {
        $estado  = $request->query('estado', 'pendiente');
        $validos = ['pendiente', 'en_progreso', 'completada'];
        if (!in_array($estado, $validos, true)) {
            return response()->json(['message' => 'Estado inválido'], 422);
        }

        if (!extension_loaded('zip') || !class_exists(\ZipArchive::class)) {
            return response()->json([
                'message' => 'La extensión ZIP de PHP no está habilitada. Activa extension=zip en php.ini y reinicia Apache.'
            ], 500);
        }

        $filename = "tareas_{$estado}_" . now()->format('Ymd_His') . ".xlsx";

        // Construir el Excel en memoria
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = ['ID','Título','Estado','Asignado a','Fecha de vencimiento','Creado'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        // Datos
        $row = 2;
        Tarea::with('user:id,nombre')
            ->where('estado', $estado)
            ->orderBy('fecha_vencimiento')
            ->chunk(500, function ($chunk) use (&$row, $sheet) {
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

        // Descargar como respuesta streaming
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // (Opcional) conserva tu exportCsv() como plan B si quieres
}
