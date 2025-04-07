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
        Schema::create('pay_payables', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->date('paymentDate')->nullable();
            $table->string('comment')->nullable();
            $table->string('nroOperacion')->nullable();
            $table->string('yape')->nullable();
            $table->string('concept')->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->decimal('cash', 10, 2)->nullable();
            $table->decimal('card', 10, 2)->nullable();
            $table->decimal('plin', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('state')->nullable()->default('1');
            $table->foreignId('bank_movement_id')->nullable()->unsigned()->constrained('bank_movements');
            $table->foreignId('bank_account_id')->nullable()->unsigned()->constrained('bank_accounts');
            $table->foreignId('payable_id')->nullable()->unsigned()->constrained('payables');
            $table->foreignId('user_created_id')->nullable()->unsigned()->constrained('users');
            $table->foreignId('bank_id')->nullable()->unsigned()->constrained('banks');
            $table->timestamps();
            $table->softDeletes(); // Soft delete column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_payables');
    }
};
