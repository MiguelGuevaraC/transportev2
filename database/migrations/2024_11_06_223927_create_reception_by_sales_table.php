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
        Schema::create('reception_by_sales', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable()->default('Activo');
            $table->foreignId('reception_id')->nullable()->unsigned()->constrained('receptions');
            $table->foreignId('moviment_id')->nullable()->unsigned()->constrained('moviments');

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
        Schema::dropIfExists('reception_by_sales');
    }
};
