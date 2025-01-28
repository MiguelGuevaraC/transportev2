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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('Disponible')->nullable();;
            $table->string('oldPlate')->nullable();
            $table->string('currentPlate')->nullable();
            $table->string('numberMtc')->nullable();
            $table->string('brand')->nullable();
            $table->string('numberModel')->nullable();
            $table->string('typeCar')->nullable();

            $table->decimal('tara', 10, 2)->nullable();
            $table->decimal('netWeight', 10, 2)->nullable();
            $table->decimal('usefulLoad', 10, 2)->nullable();
            $table->string('ownerCompany')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->integer('ejes')->nullable();
            $table->integer('wheels')->nullable();
            $table->string('color')->nullable();
            $table->integer('year')->nullable();
            $table->string('tireType')->nullable();
            $table->string('tireSuspension')->nullable();
            $table->string('bonus')->nullable();
            $table->boolean('isConection')->default(0);
    
            $table->foreignId('companyGps_id')->nullable()->unsigned()->constrained('people');
            $table->string('mode')->nullable();
            $table->foreignId('responsable_id')->nullable()->unsigned()->constrained('people');
        

            $table->foreignId('modelVehicle_id')->nullable()->unsigned()->constrained('model_functionals');
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            
            $table->foreignId('typeCarroceria_id')->nullable()->unsigned()->constrained('type_carrocerias');
            
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
        Schema::dropIfExists('vehicles');
    }
};
