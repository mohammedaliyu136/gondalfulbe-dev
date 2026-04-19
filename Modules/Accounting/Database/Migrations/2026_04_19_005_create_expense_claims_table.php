<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expense_claims')) Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_id')->unique();
            $table->unsignedBigInteger('employee_id');
            $table->date('claim_date');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'paid'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        Schema::create('expense_claim_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_claim_id');
            $table->date('date');
            $table->string('description');
            $table->unsignedBigInteger('chart_account_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('receipt_path')->nullable();
            $table->timestamps();

            $table->foreign('expense_claim_id')->references('id')->on('expense_claims')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_claim_items');
        Schema::dropIfExists('expense_claims');
    }
};
