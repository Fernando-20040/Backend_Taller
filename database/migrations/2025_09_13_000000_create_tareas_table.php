<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();

            // FK hacia 'usuarios' (tu modelo Usuario)
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();

            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();

            // Estados requeridos por la consigna
            $table->enum('estado', ['pendiente', 'en_progreso', 'completada'])->default('pendiente');

            $table->date('fecha_vencimiento')->nullable();

            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
