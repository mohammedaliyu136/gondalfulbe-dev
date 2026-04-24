<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Cooperatives\Models\Cooperative;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Gondal Fulbe ERP — Roles & Demo Users Seeder
 *
 * Creates all 11 BRD roles with scoped permissions and one demo user per role.
 * All demo users have password: Gondal@2026
 * All users are created under the company account (created_by = company user id).
 *
 * Run: php artisan db:seed --class=GondalRolesAndUsersSeeder
 */
class GondalRolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ── Resolve the company owner ────────────────────────────────────────
        $company = User::where('type', 'company')->first();
        if (! $company) {
            $this->command->error('No company user found. Run the main UsersTableSeeder first.');
            return;
        }
        $companyId = $company->id;

        // ── Ensure all module permissions exist ──────────────────────────────
        $this->ensurePermissions();

        $password = Hash::make('Gondal@2026');

        // ────────────────────────────────────────────────────────────────────
        // 1. FARMER / VENDOR  (not a login role — managed as Vender model)
        //    We create a Vender record directly, not a User.
        // ────────────────────────────────────────────────────────────────────
        $coop = Cooperative::where('created_by', $companyId)->first();

        if (! Vender::where('email', 'farmer@gondal.test')->exists()) {
            Vender::create([
                'vender_id'            => rand(10000, 99999),
                'name'                 => 'Demo Farmer',
                'email'                => 'farmer@gondal.test',
                'password'             => $password,
                'contact'              => '08012345678',
                'bank_name'            => 'Access Bank',
                'bank_code'            => '044',
                'bank_account'         => '0123456789',
                'account_name'         => 'Demo Farmer',
                'gender'               => 'M',
                'dob'                  => '1985-06-15',
                'gps_lat'              => '9.0579',
                'gps_lng'              => '12.4898',
                'digital_payment_flag' => true,
                'is_active'            => 1,
                'cooperative_id'       => $coop?->id,
                'collection_centre'    => 'Mayo',
                'created_by'           => $companyId,
                'email_verified_at'    => now(),
            ]);
            $this->command->info('✅  Farmer created: farmer@gondal.test');
        }

        // ────────────────────────────────────────────────────────────────────
        // 2. COMMUNITY COOPERATIVE LEADER
        // ────────────────────────────────────────────────────────────────────
        $coopLeaderRole = $this->upsertRole('cooperative_leader', $companyId, [
            'manage cooperative',
            'create cooperative',
            'edit cooperative',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Cooperative Leader',
            'email'      => 'coop.leader@gondal.test',
            'type'       => 'cooperative_leader',
            'password'   => $password,
            'created_by' => $companyId,
        ], $coopLeaderRole);

        // ────────────────────────────────────────────────────────────────────
        // 3. FIELD DELIVERY LEAD
        // ────────────────────────────────────────────────────────────────────
        $fieldLeadRole = $this->upsertRole('field_delivery_lead', $companyId, [
            'manage milk collection',
            'create milk collection',
            'edit milk collection',
            'manage logistics',
            'create logistics trip',
            'edit logistics trip',
            'manage requisitions',
            'create requisition',
            'manage vender',
            'create vender',
            'edit vender',
        ]);

        $this->upsertUser([
            'name'             => 'Demo Field Delivery Lead',
            'email'            => 'field.lead@gondal.test',
            'type'             => 'field_delivery_lead',
            'password'         => $password,
            'created_by'       => $companyId,
            'assigned_mcc'     => 'Mayo',
            'assigned_community' => 'Mbamba',
        ], $fieldLeadRole);

        // ────────────────────────────────────────────────────────────────────
        // 4. CENTER MANAGER
        // ────────────────────────────────────────────────────────────────────
        $centerManagerRole = $this->upsertRole('center_manager', $companyId, [
            'manage milk collection',
            'create milk collection',
            'edit milk collection',
            'manage center operations',
            'create center cost',
            'edit center cost',
            'manage requisitions',
            'create requisition',
            'edit requisition',
            'manage logistics',
            'manage oss products',
            'view reports',
            'manage reports',
        ]);

        $this->upsertUser([
            'name'         => 'Demo Center Manager',
            'email'        => 'center.manager@gondal.test',
            'type'         => 'center_manager',
            'password'     => $password,
            'created_by'   => $companyId,
            'assigned_mcc' => 'Mayo',
        ], $centerManagerRole);

        // ────────────────────────────────────────────────────────────────────
        // 5. COMPONENT LEAD  (EPO pillar manager)
        // ────────────────────────────────────────────────────────────────────
        $componentLeadRole = $this->upsertRole('component_lead', $companyId, [
            'manage requisitions',
            'approve requisition',
            'manage center operations',
            'approve center cost',
            'view reports',
            'manage reports',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Component Lead',
            'email'      => 'component.lead@gondal.test',
            'type'       => 'component_lead',
            'password'   => $password,
            'created_by' => $companyId,
        ], $componentLeadRole);

        // ────────────────────────────────────────────────────────────────────
        // 6. EXTENSION AGENT
        // ────────────────────────────────────────────────────────────────────
        $extensionAgentRole = $this->upsertRole('extension_agent', $companyId, [
            'manage extension agents',
            'create extension agents',
            'edit extension agents',
            'manage oss products',
        ]);

        $this->upsertUser([
            'name'         => 'Demo Extension Agent',
            'email'        => 'extension.agent@gondal.test',
            'type'         => 'extension_agent',
            'password'     => $password,
            'created_by'   => $companyId,
            'assigned_mcc' => 'Yola',
        ], $extensionAgentRole);

        // ────────────────────────────────────────────────────────────────────
        // 6B. MCC OFFICER
        // ────────────────────────────────────────────────────────────────────
        $mccOfficerRole = $this->upsertRole('mcc_officer', $companyId, [
            'manage milk collection',
            'create milk collection',
            'edit milk collection',
            'manage vender',
            'create vender',
            'edit vender',
            'view reports',
        ]);

        $this->upsertUser([
            'name'         => 'Demo MCC Officer',
            'email'        => 'mcc.officer@gondal.test',
            'type'         => 'mcc_officer',
            'password'     => $password,
            'created_by'   => $companyId,
            'assigned_mcc' => 'Mayo',
        ], $mccOfficerRole);

        // ────────────────────────────────────────────────────────────────────
        // 6C. LOGISTICS LEAD
        // ────────────────────────────────────────────────────────────────────
        $logisticsLeadRole = $this->upsertRole('logistics_lead', $companyId, [
            'manage logistics',
            'create logistics trip',
            'edit logistics trip',
            'manage milk collection',
            'view reports',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Logistics Lead',
            'email'      => 'logistics.lead@gondal.test',
            'type'       => 'logistics_lead',
            'password'   => $password,
            'created_by' => $companyId,
        ], $logisticsLeadRole);

        // ────────────────────────────────────────────────────────────────────
        // 6D. OSS AGENT
        // ────────────────────────────────────────────────────────────────────
        $ossAgentRole = $this->upsertRole('oss_agent', $companyId, [
            'manage oss products',
            'create oss products',
            'edit oss products',
            'manage vender',
            'view reports',
        ]);

        $this->upsertUser([
            'name'       => 'Demo OSS Agent',
            'email'      => 'oss.agent@gondal.test',
            'type'       => 'oss_agent',
            'password'   => $password,
            'created_by' => $companyId,
        ], $ossAgentRole);

        // ────────────────────────────────────────────────────────────────────
        // 7. FINANCE OFFICER
        // ────────────────────────────────────────────────────────────────────
        $financeOfficerRole = $this->upsertRole('finance_officer', $companyId, [
            'manage payment farmers',
            'generate bulk payment farmers',
            'manage requisitions',
            'approve requisition',
            'pay requisition',
            'manage center operations',
            'approve center cost',
            'pay center cost',
            'view reports',
            'manage reports',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Finance Officer',
            'email'      => 'finance.officer@gondal.test',
            'type'       => 'finance_officer',
            'password'   => $password,
            'created_by' => $companyId,
        ], $financeOfficerRole);

        // ────────────────────────────────────────────────────────────────────
        // 7B. ACCOUNTANT
        // ────────────────────────────────────────────────────────────────────
        $accountantRole = $this->upsertRole('accountant_gondal', $companyId, [
            'manage payment farmers',
            'generate bulk payment farmers',
            'view reports',
            'manage reports',
            'manage center operations',
            'approve center cost',
            'manage requisitions',
            'approve requisition',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Accountant',
            'email'      => 'accountant.gondal@gondal.test',
            'type'       => 'accountant_gondal',
            'password'   => $password,
            'created_by' => $companyId,
        ], $accountantRole);

        // ────────────────────────────────────────────────────────────────────
        // 7C. HR MANAGER
        // ────────────────────────────────────────────────────────────────────
        $hrManagerRole = $this->upsertRole('hr_manager', $companyId, [
            'manage user',
            'create user',
            'edit user',
            'view reports',
        ]);

        $this->upsertUser([
            'name'       => 'Demo HR Manager',
            'email'      => 'hr.manager@gondal.test',
            'type'       => 'hr_manager',
            'password'   => $password,
            'created_by' => $companyId,
        ], $hrManagerRole);

        // ────────────────────────────────────────────────────────────────────
        // 7D. INTERNAL AUDITOR
        // ────────────────────────────────────────────────────────────────────
        $internalAuditorRole = $this->upsertRole('internal_auditor', $companyId, [
            'view reports',
            'manage reports',
            'view executive dashboard',
            'manage milk collection',
            'manage logistics',
            'manage center operations',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Internal Auditor',
            'email'      => 'internal.auditor@gondal.test',
            'type'       => 'internal_auditor',
            'password'   => $password,
            'created_by' => $companyId,
        ], $internalAuditorRole);

        // ────────────────────────────────────────────────────────────────────
        // 8. EXECUTIVE DIRECTOR
        // ────────────────────────────────────────────────────────────────────
        $edRole = $this->upsertRole('executive_director', $companyId, [
            'manage milk collection',
            'create milk collection',
            'edit milk collection',
            'delete milk collection',
            'manage logistics',
            'create logistics trip',
            'edit logistics trip',
            'delete logistics trip',
            'manage requisitions',
            'create requisition',
            'edit requisition',
            'delete requisition',
            'approve requisition',
            'pay requisition',
            'manage center operations',
            'create center cost',
            'edit center cost',
            'delete center cost',
            'approve center cost',
            'pay center cost',
            'manage extension agents',
            'create extension agents',
            'edit extension agents',
            'delete extension agents',
            'manage oss products',
            'create oss products',
            'edit oss products',
            'delete oss products',
            'manage cooperative',
            'create cooperative',
            'edit cooperative',
            'delete cooperative',
            'manage payment farmers',
            'generate bulk payment farmers',
            'manage vender',
            'create vender',
            'edit vender',
            'delete vender',
            'view reports',
            'manage reports',
            'view executive dashboard',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Executive Director',
            'email'      => 'executive.director@gondal.test',
            'type'       => 'executive_director',
            'password'   => $password,
            'created_by' => $companyId,
        ], $edRole);

        // ────────────────────────────────────────────────────────────────────
        // 9. BOARD MEMBER  (read-only)
        // ────────────────────────────────────────────────────────────────────
        $boardRole = Role::firstOrCreate(
            ['name' => 'board_member', 'guard_name' => 'web'],
            ['created_by' => $companyId]
        );
        $boardRole->syncPermissions([
            'view reports',
            'view executive dashboard',
            'manage reports',
        ]);

        $this->upsertUser([
            'name'       => 'Demo Board Member',
            'email'      => 'board.member@gondal.test',
            'type'       => 'board_member',
            'password'   => $password,
            'created_by' => $companyId,
        ], $boardRole);

        // ────────────────────────────────────────────────────────────────────
        // 10. SYSTEM ADMINISTRATOR
        // ────────────────────────────────────────────────────────────────────
        $sysAdminRole = $this->upsertRole('system_admin', $companyId, [
            'manage user',
            'create user',
            'edit user',
            'delete user',
            'manage role',
            'create role',
            'edit role',
            'delete role',
            'manage permission',
            'create permission',
            'edit permission',
            'delete permission',
            'manage company settings',
            'manage business settings',
            'manage system settings',
            'manage milk collection',
            'create milk collection',
            'edit milk collection',
            'delete milk collection',
            'manage logistics',
            'create logistics trip',
            'edit logistics trip',
            'delete logistics trip',
            'manage requisitions',
            'create requisition',
            'edit requisition',
            'delete requisition',
            'approve requisition',
            'pay requisition',
            'manage center operations',
            'create center cost',
            'edit center cost',
            'delete center cost',
            'approve center cost',
            'pay center cost',
            'manage extension agents',
            'create extension agents',
            'edit extension agents',
            'delete extension agents',
            'manage oss products',
            'create oss products',
            'edit oss products',
            'delete oss products',
            'manage cooperative',
            'create cooperative',
            'edit cooperative',
            'delete cooperative',
            'manage payment farmers',
            'generate bulk payment farmers',
            'manage vender',
            'create vender',
            'edit vender',
            'delete vender',
            'view reports',
            'manage reports',
            'view executive dashboard',
        ]);

        $this->upsertUser([
            'name'       => 'Demo System Admin',
            'email'      => 'sys.admin@gondal.test',
            'type'       => 'system_admin',
            'password'   => $password,
            'created_by' => $companyId,
        ], $sysAdminRole);

        // ────────────────────────────────────────────────────────────────────
        // 11. RIDER  (registry entity — no login user needed per BRD)
        //     We create an App\Models\Rider record for demo purposes.
        // ────────────────────────────────────────────────────────────────────
        if (class_exists(\App\Models\Rider::class)) {
            $riderExists = \App\Models\Rider::where('email', 'rider@gondal.test')->exists();
            if (! $riderExists) {
                \App\Models\Rider::create([
                    'rider_id'      => 'RIDER-00001',
                    'name'          => 'Demo Rider',
                    'email'         => 'rider@gondal.test',
                    'password'      => $password,
                    'contact'       => '08099887766',
                    'license_no'    => 'ADE-12345AA',
                    'vehicle_type'  => 'Motorcycle',
                    'vehicle_registration' => 'YL-001-ABC',
                    'is_active'     => 1,
                    'created_by'    => $companyId,
                    'email_verified_at' => now(),
                ]);
                $this->command->info('✅  Rider registry entry created: rider@gondal.test');
            }
        }

        $this->command->info('');
        $this->command->info('Gondal Fulbe ERP — Roles & Demo Users seeded successfully.');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Farmer (Vender)',      'farmer@gondal.test',           'Gondal@2026'],
                ['Cooperative Leader',   'coop.leader@gondal.test',      'Gondal@2026'],
                ['Field Delivery Lead',  'field.lead@gondal.test',       'Gondal@2026'],
                ['Center Manager',       'center.manager@gondal.test',   'Gondal@2026'],
                ['Component Lead',       'component.lead@gondal.test',   'Gondal@2026'],
                ['Extension Agent',      'extension.agent@gondal.test',  'Gondal@2026'],
                ['MCC Officer',          'mcc.officer@gondal.test',      'Gondal@2026'],
                ['Logistics Lead',       'logistics.lead@gondal.test',   'Gondal@2026'],
                ['OSS Agent',            'oss.agent@gondal.test',        'Gondal@2026'],
                ['Finance Officer',      'finance.officer@gondal.test',  'Gondal@2026'],
                ['Accountant',           'accountant.gondal@gondal.test','Gondal@2026'],
                ['HR Manager',           'hr.manager@gondal.test',       'Gondal@2026'],
                ['Internal Auditor',     'internal.auditor@gondal.test', 'Gondal@2026'],
                ['Executive Director',   'executive.director@gondal.test','Gondal@2026'],
                ['Board Member',         'board.member@gondal.test',     'Gondal@2026'],
                ['System Admin',         'sys.admin@gondal.test',        'Gondal@2026'],
                ['Rider (registry)',     'rider@gondal.test',            'Gondal@2026 (no login)'],
            ]
        );
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function upsertRole(string $name, int $companyId, array $permissions): Role
    {
        $role = Role::firstOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            ['created_by' => $companyId]
        );
        $role->syncPermissions($permissions);
        $this->command->info("✅  Role: {$name}");
        return $role;
    }

    private function upsertUser(array $attributes, Role $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $attributes['email']],
            array_merge($attributes, [
                'lang'              => 'en',
                'avatar'            => '',
                'email_verified_at' => now(),
                'is_active'         => 1,
            ])
        );
        $user->syncRoles([$role]);
        $this->command->info("   └─ User: {$attributes['email']}");
        return $user;
    }

    private function ensurePermissions(): void
    {
        $all = [
            // MilkCollection
            'manage milk collection', 'create milk collection',
            'edit milk collection',   'delete milk collection',
            // Logistics
            'manage logistics',       'create logistics trip',
            'edit logistics trip',    'delete logistics trip',
            // Requisitions
            'manage requisitions',    'create requisition',
            'edit requisition',       'delete requisition',
            'approve requisition',    'pay requisition',
            // CenterOperations
            'manage center operations', 'create center cost',
            'edit center cost',         'delete center cost',
            'approve center cost',      'pay center cost',
            // Extension
            'manage extension agents', 'create extension agents',
            'edit extension agents',   'delete extension agents',
            // OSS
            'manage oss products',  'create oss products',
            'edit oss products',    'delete oss products',
            // Cooperatives
            'manage cooperative',  'create cooperative',
            'edit cooperative',    'delete cooperative',
            // Reports
            'view reports', 'view executive dashboard', 'manage reports',
            // Farmer payments
            'manage payment farmers', 'generate bulk payment farmers',
            // Vender / Farmer
            'manage vender', 'create vender', 'edit vender', 'delete vender',
            // User / Role / Permission (core)
            'manage user', 'create user', 'edit user', 'delete user',
            'manage role', 'create role', 'edit role', 'delete role',
            'manage permission', 'create permission', 'edit permission', 'delete permission',
            'manage company settings', 'manage business settings', 'manage system settings',
        ];

        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
