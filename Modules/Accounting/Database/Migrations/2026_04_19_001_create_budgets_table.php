<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('acct_budgets')) Schema::create('acct_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('budget_id')->unique();
            $table->string('name');
            $table->string('fiscal_year', 10);       // e.g. "2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('acct_budgets'); }
};
