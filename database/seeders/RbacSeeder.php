<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-conversations', 'reply-conversations', 'assign-conversations',
            'view-contacts', 'create-contacts', 'edit-contacts', 'delete-contacts',
            'view-channels', 'create-channels', 'edit-channels', 'delete-channels',
            'view-knowledge-bases', 'create-knowledge-bases', 'edit-knowledge-bases', 'delete-knowledge-bases',
            'manage-ai-settings',
            'view-reports', 'export-reports',
            'manage-settings', 'manage-users', 'manage-roles',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view-conversations', 'reply-conversations', 'assign-conversations',
            'view-contacts', 'create-contacts', 'edit-contacts',
            'view-channels', 'create-channels', 'edit-channels',
            'view-knowledge-bases', 'create-knowledge-bases', 'edit-knowledge-bases', 'delete-knowledge-bases',
            'view-reports', 'export-reports',
        ]);

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'view-conversations', 'reply-conversations',
            'view-contacts', 'create-contacts', 'edit-contacts',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(['view-reports', 'view-conversations']);
    }
}
