<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            if (! Schema::hasColumn('venders', 'bank_name')) {
                $table->string('bank_name', 100)->nullable();
            }
            if (! Schema::hasColumn('venders', 'bank_code')) {
                $table->string('bank_code', 10)->nullable();
            }
            if (! Schema::hasColumn('venders', 'bank_account')) {
                $table->string('bank_account', 20)->nullable();
            }
            if (! Schema::hasColumn('venders', 'account_name')) {
                $table->string('account_name', 255)->nullable();
            }
            if (! Schema::hasColumn('venders', 'cooperative_id')) {
                $table->unsignedBigInteger('cooperative_id')->nullable();
            }
            if (! Schema::hasColumn('venders', 'collection_centre')) {
                $table->string('collection_centre', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('venders', 'bank_name')        ? 'bank_name'        : null,
                Schema::hasColumn('venders', 'bank_code')        ? 'bank_code'        : null,
                Schema::hasColumn('venders', 'bank_account')     ? 'bank_account'     : null,
                Schema::hasColumn('venders', 'account_name')     ? 'account_name'     : null,
                Schema::hasColumn('venders', 'cooperative_id')   ? 'cooperative_id'   : null,
                Schema::hasColumn('venders', 'collection_centre')? 'collection_centre': null,
            ]));
        });
    }
};
