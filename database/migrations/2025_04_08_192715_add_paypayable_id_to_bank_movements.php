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
            $table->foreignId('pay_payable_id')->nullable()->unsigned()->constrained('pay_payables');
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
            if (Schema::hasColumn('bank_movements', 'pay_payable_id')) {
                $table->dropColumn('pay_payable_id');
            }
        });
    }
};
