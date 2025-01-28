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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('typeofDocument', 250)->nullable();
            $table->string('documentNumber', 250)->nullable()->unique();
            $table->string('names', 250)->nullable();
            $table->string('fatherSurname', 250)->nullable();
            $table->string('motherSurname', 250)->nullable();
            $table->date('birthDate')->nullable();
            $table->string('address', 250)->nullable();
            $table->string('telephone', 250)->nullable();
            $table->string('email', 250)->nullable();

            $table->string('businessName', 250)->nullable();
            $table->string('comercialName', 250)->nullable();
            $table->string('fiscalAddress', 250)->nullable();
            $table->string('representativePersonDni', 50)->nullable();
            $table->string('representativePersonName', 50)->nullable();
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->boolean('state')->default(true);

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
        Schema::dropIfExists('people');
    }
};
