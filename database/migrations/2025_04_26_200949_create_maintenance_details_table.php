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
        Schema::create('maintenance_details', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('quantity')->nullable();
            $table->decimal('price_total', 8, 2)->nullable();
            
            $table->foreignId('maintenance_id')->nullable()->unsigned()->constrained('maintenances');
            $table->foreignId('repuesto_id')->nullable()->unsigned()->constrained('repuestos');
           

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
        Schema::dropIfExists('maintenance_details');
    }
};
