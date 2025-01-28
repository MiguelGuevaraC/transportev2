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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('ubigeo')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('district_id')->nullable()->unsigned()->constrained('districts');
            $table->boolean('state')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Para usar deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('places');
    }
};
