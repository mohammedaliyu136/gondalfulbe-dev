<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->enum('gender', ['M', 'F', 'Other'])->nullable()->after('name');
            $table->date('dob')->nullable()->after('gender');
            $table->string('photo')->nullable()->after('dob');
            $table->decimal('gps_lat', 10, 7)->nullable()->after('photo');
            $table->decimal('gps_lng', 10, 7)->nullable()->after('gps_lat');
            $table->boolean('digital_payment_flag')->default(false)->after('gps_lng');
        });
    }

    public function down(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->dropColumn(['gender', 'dob', 'photo', 'gps_lat', 'gps_lng', 'digital_payment_flag']);
        });
    }
};
