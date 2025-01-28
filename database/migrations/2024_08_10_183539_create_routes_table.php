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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('placeStart');
            $table->string('placeEnd');

            $table->boolean('state')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('placeStart_id')->nullable()->unsigned()->constrained('places');
            $table->foreignId('placeEnd_id')->nullable()->unsigned()->constrained('places');
            $table->foreignId('routeFather_id')->nullable()->unsigned()->constrained('routes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routes');
    }
};
