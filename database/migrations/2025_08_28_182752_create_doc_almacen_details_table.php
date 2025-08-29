<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doc_almacen_details', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_almacen_id')->nullable();
            $table->integer('tire_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 8, 2)->nullable();
            $table->decimal('total_value', 8, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('doc_almacen_details');
    }
};