<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrier_guide_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_carrier_guide_id')
                ->constrained('carrier_guides')
                ->cascadeOnDelete();
            $table->foreignId('child_carrier_guide_id')
                ->constrained('carrier_guides')
                ->cascadeOnDelete();
            $table->string('relation_type', 50)->default('derivada');
            $table->timestamps();

            // Una guía hija solo puede depender de una guía matriz.
            $table->unique('child_carrier_guide_id', 'carrier_guide_relations_child_unique');
            $table->index('child_carrier_guide_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_guide_relations');
    }
};
