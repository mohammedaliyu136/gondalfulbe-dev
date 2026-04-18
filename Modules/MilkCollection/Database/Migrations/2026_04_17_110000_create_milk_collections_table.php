<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('milk_collections');
        Schema::create('milk_collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_id')->unique();
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('mcc', 50);
            $table->unsignedBigInteger('farmer_id');
            $table->decimal('quantity_litres', 10, 2);
            $table->string('quality_grade', 5); // A, B, C
            $table->decimal('temperature_celsius', 5, 2)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('collection_batch_id')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->index(['farmer_id', 'mcc', 'date', 'quality_grade', 'created_by'], 'mc_farmer_mcc_date_grade_cb');
            $table->foreign('farmer_id')->references('id')->on('venders')->onDelete('restrict');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_collections');
    }
};
