<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gondal_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_ref')->unique();
            $table->date('request_date');
            $table->unsignedBigInteger('requester_id');
            $table->string('center', 50)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_estimated_cost', 12, 2)->default(0);
            $table->string('priority', 20)->default('Medium');
            $table->string('status', 30)->default('pending');
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->tinyInteger('current_approver_level')->default(1);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->index(['status', 'center', 'priority', 'created_by']);
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('gondal_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisition_id');
            $table->string('item_name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50)->nullable();
            $table->decimal('estimated_cost', 10, 2);
            $table->text('purpose')->nullable();
            $table->timestamps();

            $table->foreign('requisition_id')->references('id')->on('gondal_requisitions')->onDelete('cascade');
        });

        Schema::create('gondal_requisition_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisition_id');
            $table->unsignedBigInteger('actor_id');
            $table->string('action', 30);
            $table->tinyInteger('level')->default(1);
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->foreign('requisition_id')->references('id')->on('gondal_requisitions')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gondal_requisition_approvals');
        Schema::dropIfExists('gondal_requisition_items');
        Schema::dropIfExists('gondal_requisitions');
    }
};
