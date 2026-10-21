<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payables', 'fuel_purchase_id')) {
            Schema::table('payables', function (Blueprint $table) {
                $table->foreignId('fuel_purchase_id')
                    ->nullable()
                    ->after('compra_moviment_id')
                    ->constrained('fuel_purchases')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payables', 'fuel_purchase_id')) {
            Schema::table('payables', function (Blueprint $table) {
                $table->dropForeign(['fuel_purchase_id']);
                $table->dropColumn('fuel_purchase_id');
            });
        }
    }
};
