<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reconciliation_items')) Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reconciliation_id');
            $table->date('date');
            $table->string('description');
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            // matched transaction_line id
            $table->unsignedBigInteger('transaction_line_id')->nullable();
            $table->boolean('is_matched')->default(false);
            $table->timestamps();

            $table->foreign('reconciliation_id')->references('id')->on('reconciliations')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('reconciliation_items'); }
};
