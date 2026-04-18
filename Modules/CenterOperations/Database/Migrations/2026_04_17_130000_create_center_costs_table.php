<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_costs', function (Blueprint $table) {
            $table->id();
            $table->string('cost_entry_id')->unique();
            $table->string('mcc', 50);
            $table->string('category', 60);
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->index(['mcc', 'status', 'category', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_costs');
    }
};
