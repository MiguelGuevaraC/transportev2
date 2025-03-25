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
        Schema::create('bank_movements', function (Blueprint $table) {
            $table->id();
            $table->string('type_moviment', 255)->nullable();
            $table->date('date_moviment')->nullable();
            $table->decimal('total_moviment', 15, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->text('comment')->nullable();
            $table->text('number_operation')->nullable();
            $table->foreignId('user_created_id')->nullable()->unsigned()->constrained('users');
            $table->foreignId('bank_id')->nullable()->unsigned()->constrained('banks');
            $table->foreignId('bank_account_id')->nullable()->unsigned()->constrained('bank_accounts');
            $table->foreignId('transaction_concept_id')->nullable()->unsigned()->constrained('transaction_concepts');
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
            $table->string('status', 255)->default('Pendiente')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_movements');
    }
};
