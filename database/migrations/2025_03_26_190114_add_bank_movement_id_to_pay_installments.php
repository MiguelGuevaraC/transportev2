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
        Schema::table('pay_installments', function (Blueprint $table) {
            $table->foreignId('bank_movement_id')->nullable()->unsigned()->constrained('bank_movements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pay_installments', function (Blueprint $table) {
            if (Schema::hasColumn('pay_installments', 'bank_movement_id')) {
                $table->dropColumn('bank_movement_id');
            }
        });
    }
};
