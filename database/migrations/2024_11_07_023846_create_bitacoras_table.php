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
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();                           // Campo para ID auto incremental
        
            $table->unsignedBigInteger('record_id'); // ID del registro afectado
            $table->string('action');                // Acción realizada (create, update, delete)
            $table->string('table_name');            // Nombre de la tabla afectada
            $table->json('data');                   // Datos en formato JSON (para almacenar datos antiguos y nuevos)
            $table->text('description');             // Descripción detallada de la actividad
            $table->ipAddress('ip_address');        // Dirección IP del usuario
            $table->text('user_agent');             // Información sobre el navegador o dispositivo
            $table->foreignId('user_id')->nullable()->unsigned()->constrained('users');
            $table->timestamps();                   // timestamps para created_at y updated_at
            $table->softDeletes();   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bitacoras');
    }
};
