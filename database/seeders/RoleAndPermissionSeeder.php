<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'admin.access',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'contacts.view',
            'contacts.create',
            'contacts.update',
            'contacts.delete',
            'tickets.view',
            'tickets.create',
            'tickets.assign',
            'tickets.update',
            'tickets.close',
            'tickets.delete',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'tasks.update',
            'tasks.complete',
            'tasks.delete',
            'events.view',
            'events.create',
            'events.update',
            'events.delete',
            'documents.view',
            'documents.upload',
            'documents.update',
            'documents.delete',
            'documents.manage_access',
            'spaces.view',
            'spaces.create',
            'spaces.update',
            'spaces.delete',
            'spaces.reserve',
            'spaces.approve_reservation',
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.delete',
            'inventory.move',
            'inventory.loan',
            'inventory.adjust',
            'hr.view',
            'hr.create',
            'hr.update',
            'hr.delete',
            'hr.approve_leave',
            'hr.validate_attendance',
            'planning.view',
            'planning.create',
            'planning.update',
            'planning.approve',
            'planning.delete',
            'reports.view',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'super_admin',
            'admin_junta',
            'executivo',
            'administrativo',
            'operacional',
            'manutencao',
            'armazem',
            'rh',
            'cidadao',
            'associacao',
            'empresa',
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        Role::findByName('super_admin', 'web')->syncPermissions(Permission::all());
        Role::findByName('admin_junta', 'web')->givePermissionTo('admin.access');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}