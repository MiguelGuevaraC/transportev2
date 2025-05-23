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
        Schema::table('bank_movements', function (Blueprint $table) {
            $table->decimal('total_anticipado_restante', 15, 2)->nullable()->default(0);
            $table->decimal('total_anticipado_egreso', 15, 2)->nullable()->default(0);
            $table->decimal('total_anticipado_egreso_restante', 15, 2)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_movements', function (Blueprint $table) {
            $table->dropColumn([
                'total_anticipado_restante',
                'total_anticipado_egreso',
                'total_anticipado_egreso_restante',
            ]);
        });
    }
};
