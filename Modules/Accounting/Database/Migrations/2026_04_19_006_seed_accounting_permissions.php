<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'manage accounting',
        // Budget
        'manage budget', 'create budget', 'edit budget', 'delete budget',
        // Reconciliation
        'manage reconciliation', 'create reconciliation', 'reconcile bank',
        // Expense Claims
        'manage expense claim', 'create expense claim', 'edit expense claim',
        'delete expense claim', 'approve expense claim', 'pay expense claim',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Grant all accounting permissions to the Owner/Admin role
        $adminRole = Role::where('name', 'Admin')->orWhere('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($this->permissions);
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', $this->permissions)->delete();
    }
};
