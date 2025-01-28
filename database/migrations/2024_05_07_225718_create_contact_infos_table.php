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
        Schema::create('contact_infos', function (Blueprint $table) {
            $table->id();
            $table->string('typeofDocument');
            $table->string('documentNumber');
            $table->string('names');
            $table->string('fatherSurname');
            $table->string('motherSurname');
            $table->string('address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');

            $table->boolean('state')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Para usar deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_infos');
    }
};
