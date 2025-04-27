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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->date('date_maintenance')->nullable();
            $table->string('mode')->nullable();
            $table->decimal('km', 8, 2)->nullable();

            $table->foreignId('vehicle_id')->nullable()->unsigned()->constrained('vehicles');
            $table->foreignId('taller_id')->nullable()->unsigned()->constrained('tallers');
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
        Schema::dropIfExists('maintenances');
    }
};
