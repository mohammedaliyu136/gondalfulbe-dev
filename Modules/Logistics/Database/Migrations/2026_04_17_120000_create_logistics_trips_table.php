<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_id')->unique();
            $table->date('trip_date');
            $table->string('mcc_source', 50);
            $table->string('destination')->default('Sebore Plant');
            $table->unsignedBigInteger('rider_id');
            $table->string('vehicle_registration')->nullable();
            $table->time('departure_time')->nullable();
            $table->time('arrival_time')->nullable();
            $table->decimal('litres_transported', 10, 2)->default(0);
            $table->string('collection_batch_id')->nullable();
            $table->decimal('fuel_cost', 10, 2)->default(0);
            $table->decimal('other_expenses', 10, 2)->default(0);
            $table->text('other_expenses_description')->nullable();
            $table->string('status', 30)->default('Scheduled');
            $table->string('delivery_note_path')->nullable();
            $table->decimal('cost_per_litre', 10, 4)->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('rider_id')->references('id')->on('riders')->onDelete('restrict');
            $table->index(['mcc_source', 'status', 'trip_date', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_trips');
    }
};
