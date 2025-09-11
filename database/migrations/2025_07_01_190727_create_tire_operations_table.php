<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tire_operations', function (Blueprint $table) {
            $table->id(); // ID de la operación
            $table->string('operation_type'); // Tipo de operación: Asignación, Cambio, Reparación, Baja, Pérdida
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles'); // Vehículo asignado
            $table->integer('position'); // Posición del neumático (1 a 12)
            $table->decimal('vehicle_km', 10, 2)->nullable(); // Kilometraje del vehículo
            $table->dateTime('operation_date'); // Fecha de operación
            $table->text('comment')->nullable(); // Comentario adicional

            $table->string('presion_aire')->nullable(); // Presión de aire

             $table->foreignId('driver_id')->nullable()->unsigned()->constrained('workers');
            $table->foreignId('user_id')->nullable()->constrained('users'); // Usuario responsable
            $table->foreignId('tire_id')->constrained('tires'); // Neumático relacionado

            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tire_operations');
    }
};
