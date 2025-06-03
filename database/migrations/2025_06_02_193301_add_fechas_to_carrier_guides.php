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
        Schema::table('carrier_guides', function (Blueprint $table) {
             $table->dateTime('date_recepcion_grt')->nullable();
            $table->dateTime('date_cargo')->nullable();
            $table->dateTime('date_est_facturacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carrier_guides', function (Blueprint $table) {
           $table->dropColumn(['date_recepcion_grt', 'date_cargo', 'date_est_facturacion']);
        });
    }
};
