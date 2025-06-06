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
        Schema::create('commision_agents', function (Blueprint $table) {
            $table->id();
            $table->decimal('paymentComission')->nullable();
            $table->boolean('state')->default(true);
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
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
        Schema::dropIfExists('commision_agents');
    }
};
