<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Creates 4 test users covering all 8 new ERP modules.
 * Run with:  php artisan db:seed --class=TestUsersSeeder
 * Re-run safely: existing users/roles are deleted and re-created.
 */
class TestUsersSeeder extends Seeder
{
    /** The company owner all sub-users belong to. */
    private int $creatorId = 2;

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Field Operations Manager ─────────────────────────────────────
        // Milk Collection + Logistics + Center Operations + Requisitions
        $this->makeUser(
            name:  'Field Ops Manager (Test)',
            email: 'fieldops@sebore.test',
            role:  'Test - Field Operations Manager',
            perms: [
                'show account dashboard',
                'manage milk collection', 'create milk collection',
                'edit milk collection',   'delete milk collection',
                'manage logistics',       'create logistics',
                'edit logistics',         'delete logistics',
                'manage center operations', 'create center operations',
                'edit center operations', 'delete center operations',
                'manage requisitions',    'create requisition',
                'edit requisition',       'delete requisition',
                'approve requisition',
            ],
        );

        // ── 2. OSS & Extension Officer ───────────────────────────────────────
        // One Stop Shop + Extension + Sponsors
        $this->makeUser(
            name:  'OSS Extension Officer (Test)',
            email: 'ossext@sebore.test',
            role:  'Test - OSS & Extension Officer',
            perms: [
                'show account dashboard',
                'manage oss products', 'create oss products',
                'edit oss products',   'delete oss products',
                'manage extension agents', 'create extension agents',
                'edit extension agents',   'delete extension agents',
                'manage sponsors',    'create sponsors',
                'edit sponsors',      'delete sponsors',
            ],
        );

        // ── 3. Reports Viewer ────────────────────────────────────────────────
        // Read-only access to all reports
        $this->makeUser(
            name:  'Reports Viewer (Test)',
            email: 'reports@sebore.test',
            role:  'Test - Reports Viewer',
            perms: [
                'show account dashboard',
                'view reports',
                'view executive dashboard',
            ],
        );

        // ── 4. ERP Super Admin ───────────────────────────────────────────────
        // Full access to ALL 8 new modules
        $this->makeUser(
            name:  'ERP Super Admin (Test)',
            email: 'erpadmin@sebore.test',
            role:  'Test - ERP Super Admin',
            perms: [
                'show account dashboard',
                'manage milk collection', 'create milk collection',
                'edit milk collection',   'delete milk collection',
                'manage logistics',       'create logistics',
                'edit logistics',         'delete logistics',
                'manage center operations', 'create center operations',
                'edit center operations', 'delete center operations',
                'manage requisitions',    'create requisition',
                'edit requisition',       'delete requisition',
                'approve requisition',
                'manage oss products',    'create oss products',
                'edit oss products',      'delete oss products',
                'manage extension agents','create extension agents',
                'edit extension agents',  'delete extension agents',
                'manage sponsors',        'create sponsors',
                'edit sponsors',          'delete sponsors',
                'view reports',           'view executive dashboard',
            ],
        );

        $this->command->info('');
        $this->command->info('✅  Test users created successfully:');
        $this->command->table(
            ['Name', 'Email', 'Password', 'Role'],
            [
                ['Field Ops Manager (Test)',    'fieldops@sebore.test',  'Password@123', 'Test - Field Operations Manager'],
                ['OSS Extension Officer (Test)', 'ossext@sebore.test',   'Password@123', 'Test - OSS & Extension Officer'],
                ['Reports Viewer (Test)',        'reports@sebore.test',  'Password@123', 'Test - Reports Viewer'],
                ['ERP Super Admin (Test)',       'erpadmin@sebore.test', 'Password@123', 'Test - ERP Super Admin'],
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function makeUser(string $name, string $email, string $role, array $perms): void
    {
        // Remove any previous test run for this email/role
        User::where('email', $email)->delete();
        $existingRole = Role::where('name', $role)->where('created_by', $this->creatorId)->first();
        if ($existingRole) {
            $existingRole->syncPermissions([]);
            $existingRole->delete();
        }

        // Create role and assign permissions (only those that actually exist in DB)
        $roleModel = Role::create([
            'name'       => $role,
            'created_by' => $this->creatorId,
            'guard_name' => 'web',
        ]);

        $existingPerms = Permission::whereIn('name', $perms)->pluck('name')->toArray();
        $roleModel->syncPermissions($existingPerms);

        // Create user
        $user = User::create([
            'name'               => $name,
            'email'              => $email,
            'password'           => Hash::make('Password@123'),
            'type'               => 'company',   // sub-account (not super-admin)
            'is_active'          => 1,
            'is_disable'         => 0,           // 0 = enabled (1 = disabled); must be explicit
            'is_enable_login'    => 1,
            'email_verified_at'  => now(),
            'created_by'         => $this->creatorId,
            'lang'               => 'en',
            'plan'               => 1,           // Free Plan (id=1); required for dashboard queries
        ]);

        $user->assignRole($roleModel);

        $this->command->line("  Created: {$email}  →  role: {$role}  ({$roleModel->permissions()->count()} permissions)");
    }
}
