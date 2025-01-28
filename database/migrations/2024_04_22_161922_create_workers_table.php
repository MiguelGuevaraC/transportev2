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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->nullable();
            $table->string('department', 100)->nullable()->nullable();
            $table->string('province', 100)->nullable()->nullable();
            $table->string('district', 100)->nullable()->nullable();
            $table->string('maritalStatus', 100)->nullable()->nullable();
            $table->string('levelInstitution', 100)->nullable()->nullable();
            $table->string('occupation', 100)->nullable()->nullable();
            $table->string('licencia', 100)->nullable()->nullable();
            $table->string('pathPhoto', 100)->nullable()->nullable();
            // $table->string('position', 100)->nullable()->nullable();
            // $table->string('center', 100)->nullable()->nullable();
            // $table->string('typeRelationship', 100)->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
            $table->foreignId('area_id')->nullable()->unsigned()->constrained('areas');
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->foreignId('district_id')->nullable()->unsigned()->constrained('districts');
            
            $table->boolean('state')->default(true);
            $table->string('status')->default('Disponible')->nullable();;
            
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
        Schema::dropIfExists('workers');
    }
};
