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
        Schema::create('detail_grts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('carrierGuide_id')->nullable()->unsigned()->constrained('carrier_guides');
            $table->foreignId('detailReception_id')->unsigned()->constrained('detail_receptions');

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
        Schema::dropIfExists('detail_grts');
    }
};
