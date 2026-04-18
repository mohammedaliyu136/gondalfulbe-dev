<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oss_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('name');
            $table->string('category', 30); // Feed|Veterinary|Equipment|Other
            $table->string('unit', 30);
            $table->decimal('price', 10, 2);
            $table->decimal('reorder_level', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('supplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('oss_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('type', 20); // Stock In|Stock Out|Adjustment
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->date('date');
            $table->string('center', 50)->nullable();
            $table->string('reference')->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('oss_products')->onDelete('restrict');
            $table->index(['product_id', 'type', 'center', 'date']);
        });

        Schema::create('oss_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_id')->unique();
            $table->date('date');
            $table->unsignedBigInteger('farmer_id');
            $table->string('center', 50)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method', 20)->default('Cash');
            $table->boolean('is_credit')->default(false);
            $table->boolean('credit_settled')->default(false);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('farmer_id')->references('id')->on('venders')->onDelete('restrict');
        });

        Schema::create('oss_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('oss_sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('oss_products')->onDelete('restrict');
        });

        Schema::create('oss_agent_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('allocation_id')->unique();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_allocated', 10, 2);
            $table->date('allocated_date');
            $table->unsignedBigInteger('allocated_by');
            $table->string('center', 50)->nullable();
            $table->unsignedBigInteger('reference_stock_out_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('oss_products')->onDelete('restrict');
            $table->index(['agent_id', 'product_id']);
        });

        Schema::create('oss_agent_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_id')->unique();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('farmer_id');
            $table->date('date');
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method', 20)->default('Cash');
            $table->boolean('is_credit')->default(false);
            $table->unsignedBigInteger('visit_id')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('farmer_id')->references('id')->on('venders')->onDelete('restrict');
            $table->index(['agent_id', 'date']);
        });

        Schema::create('oss_agent_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_sale_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('agent_sale_id')->references('id')->on('oss_agent_sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('oss_products')->onDelete('restrict');
        });

        Schema::create('oss_agent_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_id')->unique();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_returned', 10, 2);
            $table->date('return_date');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('reference_stock_in_id')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('oss_products')->onDelete('restrict');
            $table->index(['agent_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oss_agent_returns');
        Schema::dropIfExists('oss_agent_sale_items');
        Schema::dropIfExists('oss_agent_sales');
        Schema::dropIfExists('oss_agent_allocations');
        Schema::dropIfExists('oss_sale_items');
        Schema::dropIfExists('oss_sales');
        Schema::dropIfExists('oss_inventory');
        Schema::dropIfExists('oss_products');
    }
};
