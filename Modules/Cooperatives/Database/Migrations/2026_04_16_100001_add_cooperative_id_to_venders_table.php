<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->unsignedBigInteger('cooperative_id')->nullable()->after('collection_centre');
            $table->foreign('cooperative_id')
                  ->references('id')
                  ->on('cooperatives')
                  ->nullOnDelete();   // keep farmer records if cooperative is deleted
        });
    }

    public function down(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->dropForeign(['cooperative_id']);
            $table->dropColumn('cooperative_id');
        });
    }
};
