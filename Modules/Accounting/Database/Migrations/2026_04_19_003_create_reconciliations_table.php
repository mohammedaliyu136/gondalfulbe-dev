<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reconciliations')) Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('reconciliation_id')->unique();
            $table->unsignedBigInteger('bank_account_id');
            $table->date('statement_date');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->enum('status', ['open', 'reconciled'])->default('open');
            $table->timestamp('reconciled_at')->nullable();
            $table->unsignedBigInteger('reconciled_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('reconciliations'); }
};
