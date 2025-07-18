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
        Schema::create('tires', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // Código único del neumático
            $table->string('condition')->nullable(); // Condición: Nuevo, Usado, Reencauchado
            $table->integer('retread_number')->nullable(); // Número de reencauche
            $table->date('entry_date'); // Fecha de ingreso
            $table->foreignId('supplier_id')->nullable()->unsigned()->constrained('people');// ID del proveedor
            $table->foreignId('vehicle_id')->nullable()->unsigned()->constrained('vehicles');// ID del proveedor
            $table->integer('position_vehicle')->nullable();

            $table->string('material')->nullable(); // Material del neumático
            $table->string('brand')->nullable(); // Marca del neumático
            $table->string('design')->nullable(); // Diseño del neumático
            $table->string('type')->nullable(); // Tipo de neumático
            $table->string('size')->nullable(); // Medida del neumático
            $table->string('dot')->nullable(); // Fecha de fabricación (DOT)
            $table->string('tread_type')->nullable(); // Tipo de banda
            $table->decimal('current_tread', 10, 2)->nullable(); // Cocada actual
            $table->decimal('minimum_tread', 10, 2)->nullable(); // Cocada mínima permitida
            $table->decimal('tread', 10, 2)->nullable(); // Cocada inicial o general
            $table->decimal('shoulder1', 10, 2)->nullable(); // Ribete 1
            $table->decimal('shoulder2', 10, 2)->nullable(); // Ribete 2
            $table->decimal('shoulder3', 10, 2)->nullable(); // Ribete 3

            $table->timestamps(); // created_at y updated_at
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
        Schema::dropIfExists('tires');
    }
};
