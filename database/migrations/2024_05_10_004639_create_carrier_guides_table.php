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
        Schema::create('carrier_guides', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('Pendiente');
            $table->string('numero')->nullable();
            $table->string('observation')->nullable();

            $table->string('document')->nullable();
            // $table->string('reasonForTransfer')->nullable();
            $table->dateTime('transferStartDate')->nullable();
            $table->dateTime('transferDateEstimated')->nullable();
            // $table->string('carrier')->nullable();

            $table->string('ubigeoStart')->nullable();
            $table->string('ubigeoEnd')->nullable();
            $table->string('addressStart')->nullable();
            $table->string('addressEnd')->nullable();
         

            $table->foreignId('tract_id')->nullable()->unsigned()->constrained('vehicles');
            $table->foreignId('platform_id')->nullable()->unsigned()->constrained('vehicles');
            $table->foreignId('origin_id')->nullable()->unsigned()->constrained('places');
            $table->foreignId('destination_id')->nullable()->unsigned()->constrained('places');
            $table->foreignId('sender_id')->nullable()->unsigned()->constrained('people');
            $table->foreignId('recipient_id')->nullable()->unsigned()->constrained('people');
            $table->foreignId('payResponsible_id')->nullable()->unsigned()->constrained('people');

            $table->foreignId('districtStart_id')->nullable()->unsigned()->constrained('districts');
            $table->foreignId('districtEnd_id')->nullable()->unsigned()->constrained('districts');

            $table->foreignId('driver_id')->nullable()->unsigned()->constrained('workers');
            $table->foreignId('copilot_id')->nullable()->unsigned()->constrained('workers');

            $table->foreignId('subcontract_id')->nullable()->unsigned()->constrained('subcontracts');

            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->foreignId('programming_id')->nullable()->unsigned()->constrained('programmings');
            $table->foreignId('motive_id')->nullable()->unsigned()->constrained('motives');

            $table->foreignId('reception_id')->nullable()->unsigned()->constrained('receptions');

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
        Schema::dropIfExists('carrier_guides');
    }
};
