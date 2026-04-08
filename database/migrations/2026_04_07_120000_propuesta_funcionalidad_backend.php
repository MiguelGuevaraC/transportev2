<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_list_items', function (Blueprint $table) {
            $table->foreignId('repuesto_id')->nullable()->constrained('repuestos');
        });

        Schema::create('product_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_list_id')->constrained('check_lists');
            $table->foreignId('branch_office_id')->nullable()->constrained('branch_offices');
            $table->string('status')->default('BORRADOR');
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_requirement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_requirement_id')->constrained('product_requirements')->cascadeOnDelete();
            $table->foreignId('check_list_detail_id')->nullable()->constrained('check_list_details')->nullOnDelete();
            $table->foreignId('repuesto_id')->constrained('repuestos');
            $table->decimal('quantity_requested', 14, 4);
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_requirement_id')->constrained('product_requirements')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->constrained('people');
            $table->boolean('is_winner')->default(false);
            $table->string('status')->default('REGISTRADA');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['product_requirement_id', 'proveedor_id'], 'uniq_cotiz_proveedor_req');
        });

        Schema::create('purchase_quotation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_quotation_id')->constrained('purchase_quotations')->cascadeOnDelete();
            $table->foreignId('repuesto_id')->constrained('repuestos');
            $table->decimal('quantity', 14, 4);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('subtotal', 16, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('compra_partial_receipt_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_office_id')->constrained('branch_offices');
            $table->foreignId('proveedor_id')->constrained('people');
            $table->foreignId('invoice_compra_moviment_id')->nullable()->constrained('compra_moviments')->nullOnDelete();
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('compra_orders', function (Blueprint $table) {
            $table->foreignId('purchase_quotation_id')->nullable()->after('proveedor_id')->constrained('purchase_quotations')->nullOnDelete();
        });

        Schema::table('compra_order_details', function (Blueprint $table) {
            $table->decimal('quantity_received', 14, 4)->default(0)->after('quantity');
        });

        Schema::table('compra_moviments', function (Blueprint $table) {
            $table->boolean('is_partial')->default(false)->after('compra_order_id');
            $table->foreignId('partial_receipt_group_id')->nullable()->after('is_partial')->constrained('compra_partial_receipt_groups')->nullOnDelete();
        });

        Schema::table('compra_moviment_details', function (Blueprint $table) {
            $table->foreignId('compra_order_detail_id')->nullable()->after('compra_moviment_id')->constrained('compra_order_details')->nullOnDelete();
        });

        Schema::create('maintenance_form_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('group_menu_id')->constrained('group_menus');
            $table->foreignId('typeof_user_id')->constrained('typeof_Users');
            $table->boolean('allowed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'group_menu_id', 'typeof_user_id'], 'uniq_maint_action_menu_type');
        });

        Schema::create('worker_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('action', 20);
            $table->text('reason')->nullable();
            $table->date('effective_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->after('vehicle_id')->constrained('workers')->nullOnDelete();
            $table->foreignId('type_document_id')->nullable()->after('type')->constrained('type_documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->dropForeign(['type_document_id']);
            $table->dropColumn(['worker_id', 'type_document_id']);
        });

        Schema::dropIfExists('worker_status_histories');
        Schema::dropIfExists('maintenance_form_actions');

        Schema::table('compra_moviment_details', function (Blueprint $table) {
            $table->dropForeign(['compra_order_detail_id']);
            $table->dropColumn('compra_order_detail_id');
        });

        Schema::table('compra_moviments', function (Blueprint $table) {
            $table->dropForeign(['partial_receipt_group_id']);
            $table->dropColumn(['is_partial', 'partial_receipt_group_id']);
        });

        Schema::table('compra_order_details', function (Blueprint $table) {
            $table->dropColumn('quantity_received');
        });

        Schema::table('compra_orders', function (Blueprint $table) {
            $table->dropForeign(['purchase_quotation_id']);
            $table->dropColumn('purchase_quotation_id');
        });

        Schema::dropIfExists('compra_partial_receipt_groups');
        Schema::dropIfExists('purchase_quotation_lines');
        Schema::dropIfExists('purchase_quotations');
        Schema::dropIfExists('product_requirement_lines');
        Schema::dropIfExists('product_requirements');

        Schema::table('check_list_items', function (Blueprint $table) {
            $table->dropForeign(['repuesto_id']);
            $table->dropColumn('repuesto_id');
        });
    }
};
