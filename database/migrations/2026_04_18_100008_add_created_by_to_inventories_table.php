<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventories') && ! Schema::hasColumn('inventories', 'created_by')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('reorder_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inventories', 'created_by')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};
