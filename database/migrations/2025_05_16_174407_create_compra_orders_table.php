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
        Schema::create('compra_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->dateTime('date_movement')->nullable();
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
            $table->foreignId('proveedor_id')->nullable()->unsigned()->constrained('people');
            $table->text('comment')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('compra_orders');
    }
};
