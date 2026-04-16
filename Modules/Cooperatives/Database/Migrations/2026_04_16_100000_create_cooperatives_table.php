<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name')->unique();          // DB-level uniqueness (not just app-level)
            $table->string('location')->nullable();    // MCC / milk collection centre name
            $table->string('leader_name')->nullable();
            $table->string('leader_phone', 20)->nullable();
            $table->string('site_location')->nullable();
            $table->date('formation_date')->nullable();
            $table->decimal('average_daily_supply', 10, 2)->default(0);
            $table->string('status', 20)->default('active'); // 'active' | 'inactive'
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperatives');
    }
};
