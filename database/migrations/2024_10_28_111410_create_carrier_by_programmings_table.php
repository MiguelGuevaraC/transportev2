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
        Schema::create('carrier_by_programmings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrierGuide_id')->nullable()->unsigned()->constrained('carrier_guides');
            $table->foreignId('programming_id')->unsigned()->constrained('programmings');

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
        Schema::dropIfExists('carrier_by_programmings');
    }
};
