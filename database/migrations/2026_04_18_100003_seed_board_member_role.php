<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::firstOrCreate(['name' => 'board_member', 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'manage reports', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);
    }

    public function down(): void
    {
        Role::where('name', 'board_member')->delete();
    }
};
