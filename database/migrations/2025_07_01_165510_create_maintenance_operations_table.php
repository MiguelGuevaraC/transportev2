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
        Schema::create('maintenance_operations', function (Blueprint $table) {
         $table->id();
            $table->string('name')->nullable();
            $table->string('type_moviment')->nullable();
            $table->decimal('quantity', 8, 2)->nullable();
            $table->string('unity')->nullable();
            $table->foreignId('maintenance_id')->nullable()->unsigned()->constrained('maintenances');
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
        Schema::dropIfExists('maintenance_operations');
    }
};
