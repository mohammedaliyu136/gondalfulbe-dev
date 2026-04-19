<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('budget_lines')) Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('chart_account_id');
            $table->string('description')->nullable();
            $table->decimal('jan', 15, 2)->default(0);
            $table->decimal('feb', 15, 2)->default(0);
            $table->decimal('mar', 15, 2)->default(0);
            $table->decimal('apr', 15, 2)->default(0);
            $table->decimal('may', 15, 2)->default(0);
            $table->decimal('jun', 15, 2)->default(0);
            $table->decimal('jul', 15, 2)->default(0);
            $table->decimal('aug', 15, 2)->default(0);
            $table->decimal('sep', 15, 2)->default(0);
            $table->decimal('oct', 15, 2)->default(0);
            $table->decimal('nov', 15, 2)->default(0);
            $table->decimal('dec', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('budget_lines'); }
};
