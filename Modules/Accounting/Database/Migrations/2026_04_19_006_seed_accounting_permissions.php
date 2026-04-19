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

        // Grant all accounting permissions to admin/finance roles
        $targets = ['super admin', 'Super Admin', 'system_admin', 'IT Admin', 'Admin', 'admin', 'company', 'accountant', 'Accountant II', 'finance_officer'];
        foreach ($targets as $name) {
            $role = Role::where('name', $name)->first();
            if ($role) {
                $role->givePermissionTo($this->permissions);
            }
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', $this->permissions)->delete();
    }
};
