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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->string('reason')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('totalReferido', 10, 2)->nullable();
            $table->string('status')->nullable()->default('Pendiente');

            $table->string('comment')->nullable();
            $table->string('description')->nullable();
            $table->string('percentage')->nullable();

            $table->string('productList')->nullable();

            $table->foreignId('moviment_id')->nullable()->unsigned()->constrained('moviments');
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');

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
        Schema::dropIfExists('credit_notes');
    }
};
