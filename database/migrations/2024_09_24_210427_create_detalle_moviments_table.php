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
        Schema::create('detalle_moviments', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->string('placa')->nullable();
            $table->string('guia')->nullable();
            $table->string('os')->nullable();
            $table->string('cantidad')->nullable();
            $table->foreignId('tract_id')->nullable()->unsigned()->constrained('vehicles');
            $table->foreignId('carrier_guide_id')->nullable()->unsigned()->constrained('carrier_guides');

            $table->decimal('precioCompra', 10, 2)->nullable();
            $table->decimal('precioVenta', 10, 2)->nullable();
            $table->foreignId('reception_id')->unsigned()->constrained('receptions');
            
            $table->foreignId('moviment_id')->nullable()->unsigned()->constrained('moviments');

            $table->timestamps();
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
        Schema::dropIfExists('detalle_moviments');
    }
};
