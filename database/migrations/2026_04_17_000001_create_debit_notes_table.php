<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->string('reason')->nullable();
            $table->string('reason_code', 2)->nullable()
                ->comment('SUNAT Cat. 10: 01 intereses, 02 aumento en el valor, 03 penalidades');
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('totalReferido', 10, 2)->nullable();
            $table->date('newDate')->nullable();
            $table->decimal('newTotal', 10, 2)->nullable();
            $table->decimal('totalAjuste', 10, 2)->nullable();
            $table->date('fechaAjuste')->nullable();
            $table->string('status')->nullable()->default('Pendiente');
            $table->string('status_facturado')->nullable()->default('Pendiente');
            $table->string('getstatus_fact')->nullable();
            $table->string('comment')->nullable();
            $table->string('description')->nullable();
            $table->string('percentaje')->nullable();
            $table->boolean('state')->nullable();
            $table->string('productList')->nullable();
            $table->foreignId('moviment_id')->nullable()->unsigned()->constrained('moviments');
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('debit_notes');
    }
};
