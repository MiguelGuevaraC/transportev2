<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tires', function (Blueprint $table) {
            $table->foreignId('tire_measure_id')->nullable()->constrained('tire_measures');
            $table->string('number_fact')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tires', function (Blueprint $table) {
            $table->dropForeign(['tire_measure_id']);
            $table->dropColumn('tire_measure_id');
            $table->dropColumn('number_fact');
        });
    }
};
