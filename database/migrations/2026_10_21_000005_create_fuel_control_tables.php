<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('ruc', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->unsignedInteger('credit_days')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['active', 'name']);
        });

        Schema::create('fuel_vehicle_driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('worker_id')->constrained('workers');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['vehicle_id', 'active']);
            $table->index(['worker_id', 'active']);
        });

        Schema::create('fuel_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_station_id')->constrained('fuel_stations');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('worker_id')->constrained('workers');
            $table->foreignId('assignment_id')->nullable()->constrained('fuel_vehicle_driver_assignments')->nullOnDelete();
            $table->date('fuel_date');
            $table->decimal('odometer_km', 12, 2);
            $table->string('fuel_type', 50)->default('Petróleo');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 4);
            $table->decimal('total', 12, 2);
            $table->string('payment_condition', 20); // Contado | Crédito
            $table->string('payment_method', 50)->nullable();
            $table->date('paid_at')->nullable();
            $table->string('payment_operation_number', 100)->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('odometer_image')->nullable();
            $table->string('pump_start_image')->nullable();
            $table->string('pump_end_image')->nullable();
            $table->text('observation')->nullable();
            $table->string('status', 30)->default('Registrado');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['fuel_date', 'vehicle_id']);
            $table->index(['payment_condition', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_purchases');
        Schema::dropIfExists('fuel_vehicle_driver_assignments');
        Schema::dropIfExists('fuel_stations');
    }
};
