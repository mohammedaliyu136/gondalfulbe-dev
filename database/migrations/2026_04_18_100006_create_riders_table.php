<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('riders')) {
            return;
        }

        Schema::table('riders', function (Blueprint $table) {
            if (! Schema::hasColumn('riders', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (! Schema::hasColumn('riders', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            }
            if (! Schema::hasColumn('riders', 'license_no')) {
                $table->string('license_no', 50)->nullable();
            }
            if (! Schema::hasColumn('riders', 'vehicle_type')) {
                $table->string('vehicle_type', 100)->nullable();
            }
            if (! Schema::hasColumn('riders', 'vehicle_registration')) {
                $table->string('vehicle_registration', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('riders', 'password')             ? 'password'             : null,
                Schema::hasColumn('riders', 'email_verified_at')    ? 'email_verified_at'    : null,
                Schema::hasColumn('riders', 'license_no')           ? 'license_no'           : null,
                Schema::hasColumn('riders', 'vehicle_type')         ? 'vehicle_type'         : null,
                Schema::hasColumn('riders', 'vehicle_registration') ? 'vehicle_registration' : null,
            ]));
        });
    }
};
