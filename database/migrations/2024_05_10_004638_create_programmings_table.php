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
        Schema::create('programmings', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->dateTime('departureDate')->nullable();
            $table->dateTime('estimatedArrivalDate')->nullable();
            $table->dateTime('actualArrivalDate')->nullable();

            $table->string('state')->nullable()->default(1);
            $table->boolean('isload')->nullable()->default(0);

            $table->decimal('totalWeight', 10, 2)->nullable()->default(0);
            $table->integer('carrierQuantity')->nullable()->default(0);
            $table->integer('detailQuantity')->nullable()->default(0);
            $table->decimal('totalAmount', 10, 2)->nullable()->default(0);

            $table->decimal('kmStart', 10, 2)->nullable()->default(0);
            $table->decimal('kmEnd', 10, 2)->nullable()->default(0);
            $table->decimal('totalExpenses', 10, 2)->nullable()->default(0);
            $table->decimal('totalReturned', 10, 2)->nullable()->default(0);
            $table->decimal('totalViaje', 10, 2)->nullable()->default(0);

            $table->foreignId('origin_id')->nullable()->unsigned()->constrained('places');
            $table->foreignId('destination_id')->nullable()->unsigned()->constrained('places');

            $table->foreignId('tract_id')->nullable()->unsigned()->constrained('vehicles');
            $table->foreignId('platForm_id')->nullable()->unsigned()->constrained('vehicles');
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->string('status', 255)->default('Generada')->nullable();

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
        Schema::dropIfExists('programmings');
    }
};
