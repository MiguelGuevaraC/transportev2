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
        Schema::table('detail_receptions', function (Blueprint $table) {
            $table->foreignId('tarifa_id')->nullable()->unsigned()->constrained('tarifarios');
        });
    }

    public function down()
    {
        Schema::table('detail_receptions', function (Blueprint $table) {
            $table->dropForeign(['tarifa_id']);
            $table->dropColumn('tarifa_id');
        });
    }
};
