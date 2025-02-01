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
        Schema::create('tarifarios', function (Blueprint $table) {
            $table->id();
            
            $table->decimal('tarifa', 10, 2)->nullable(); // Balance de stock
            $table->text('description')->nullable();

            $table->foreignId('unity_id')->nullable()->unsigned()->constrained('unities');
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarifarios');
    }
};
