<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_yadira_movements', function (Blueprint $table) {
            $table->id();
            $table->date('movement_date');
            $table->time('movement_time')->nullable();
            $table->string('type', 20); // Ingreso | Egreso
            $table->string('operation_number')->nullable();
            $table->decimal('amount', 12, 2);
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('concept', 255);
            $table->text('observation')->nullable();
            $table->string('voucher_path')->nullable();
            $table->foreignId('driver_expense_id')->nullable()->constrained('driver_expenses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['movement_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_yadira_movements');
    }
};
