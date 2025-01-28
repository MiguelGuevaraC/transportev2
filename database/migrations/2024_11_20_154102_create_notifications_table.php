<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id(); // Campo 'id' como clave primaria
            $table->unsignedBigInteger('record_id')->nullable(); // Relación opcional con otra tabla
            $table->string('title')->nullable(); // Título de la notificación
            $table->text('message')->nullable(); // Mensaje de la notificación
            $table->string('type')->nullable(); // Tipo de notificación (ej. warning, info)
            $table->string('table')->nullable(); // Nombre de la tabla relacionada
            $table->string('priority')->default('low')->nullable(); // Nivel de prioridad (default: bajo)
            $table->timestamps(); // Campos 'created_at' y 'updated_at'

            // Índices y claves foráneas opcionales
            $table->index('record_id')->nullable(); // Índice para búsquedas rápidas por 'record_id'
       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
