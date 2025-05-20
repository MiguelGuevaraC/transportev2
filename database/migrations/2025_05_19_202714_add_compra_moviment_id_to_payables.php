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
        Schema::table('payables', function (Blueprint $table) {
            $table->foreignId('compra_moviment_id')->nullable()->unsigned()->constrained('compra_moviments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->dropForeign(['compra_moviment_id']);
            $table->dropColumn('compra_moviment_id');
        });
    }
};
