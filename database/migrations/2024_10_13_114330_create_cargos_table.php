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
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->string('pathFile')->nullable();
            $table->boolean('state')->default(true);
            $table->string('status')->nullable();
            $table->foreignId('reception_id')->nullable()->unsigned()->constrained('receptions');
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
        Schema::dropIfExists('cargos');
    }
};
