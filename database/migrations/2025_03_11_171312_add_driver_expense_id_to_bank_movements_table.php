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
            $table->foreignId('driver_expense_id')->nullable()->unsigned()->constrained('driver_expenses');
            $table->foreignId('pay_installment_id')->nullable()->unsigned()->constrained('pay_installments');
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
            if (Schema::hasColumn('bank_movements', 'driver_expense_id')) {
                $table->dropColumn('driver_expense_id');
            }
            if (Schema::hasColumn('bank_movements', 'pay_installment_id')) {
                $table->dropColumn('pay_installment_id');
            }
        });
    }
};
