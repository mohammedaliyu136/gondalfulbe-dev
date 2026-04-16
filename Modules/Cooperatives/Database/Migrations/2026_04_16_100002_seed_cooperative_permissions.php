<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Cooperative-specific permissions, kept separate from the generic 'vender'
     * permissions so that cooperative management can be granted independently.
     */
    private array $permissions = [
        'manage cooperative',
        'create cooperative',
        'edit cooperative',
        'delete cooperative',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', $this->permissions)->delete();
    }
};
