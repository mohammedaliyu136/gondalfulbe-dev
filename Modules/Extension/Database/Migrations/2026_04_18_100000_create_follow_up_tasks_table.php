<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('farmer_id')->nullable();
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->unsignedInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('visit_id')->references('id')->on('field_visits')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_tasks');
    }
};
