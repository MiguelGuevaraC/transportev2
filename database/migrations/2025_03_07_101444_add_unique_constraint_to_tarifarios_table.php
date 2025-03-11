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
        Schema::table('tarifarios', function (Blueprint $table) {
            // Crear un índice único compuesto con los campos relevantes
            $table->unique([
                'person_id',
                'origin_id',
                'unity_id',
                'destination_id',
                'limitweight_min',
                'limitweight_max',
                'deleted_at'
            ], 'unique_tarifa_ranges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->dropUnique('unique_tarifa_ranges');
        });
    }
};
