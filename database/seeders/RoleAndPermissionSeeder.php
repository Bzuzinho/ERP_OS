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
            'documents.download',
            'documents.manage_access',
            'documents.approve',
            'document_types.view',
            'document_types.create',
            'document_types.update',
            'document_types.delete',
            'meeting_minutes.view',
            'meeting_minutes.create',
            'meeting_minutes.update',
            'meeting_minutes.approve',
            'meeting_minutes.delete',
            'spaces.view',
            'spaces.create',
            'spaces.update',
            'spaces.delete',
            'spaces.reserve',
            'spaces.approve_reservation',
            'spaces.cancel_reservation',
            'spaces.manage_maintenance',
            'spaces.manage_cleaning',
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.delete',
            'inventory.move',
            'inventory.loan',
            'inventory.return',
            'inventory.adjust',
            'inventory.breakage',
            'inventory.restock',
            'inventory.approve_restock',
            'inventory.manage_categories',
            'inventory.manage_locations',
            'hr.view',
            'hr.create',
            'hr.update',
            'hr.delete',
            'hr.manage_departments',
            'hr.manage_teams',
            'hr.manage_schedules',
            'hr.view_attendance',
            'hr.create_attendance',
            'hr.validate_attendance',
            'hr.view_leave',
            'hr.create_leave',
            'hr.approve_leave',
            'hr.reject_leave',
            'hr.assign_employees',
            'planning.view',
            'planning.create',
            'planning.update',
            'planning.approve',
            'planning.cancel',
            'planning.complete',
            'planning.delete',
            'planning.manage_tasks',
            'planning.manage_resources',
            'planning.manage_recurring',
            'planning.execute_recurring',
            'reports.view',
            'reports.export',
            'reports.tickets',
            'reports.tasks',
            'reports.events',
            'reports.spaces',
            'reports.inventory',
            'reports.hr',
            'reports.planning',
            'reports.documents',
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
        Role::findByName('admin_junta', 'web')->syncPermissions([
            'admin.access',
            'contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete',
            'tickets.view', 'tickets.create', 'tickets.assign', 'tickets.update', 'tickets.close', 'tickets.delete',
            'tasks.view', 'tasks.create', 'tasks.assign', 'tasks.update', 'tasks.complete', 'tasks.delete',
            'events.view', 'events.create', 'events.update', 'events.delete',
            'documents.view', 'documents.upload', 'documents.update', 'documents.delete', 'documents.download', 'documents.manage_access', 'documents.approve',
            'document_types.view', 'document_types.create', 'document_types.update', 'document_types.delete',
            'meeting_minutes.view', 'meeting_minutes.create', 'meeting_minutes.update', 'meeting_minutes.approve', 'meeting_minutes.delete',
            'spaces.view', 'spaces.create', 'spaces.update', 'spaces.delete', 'spaces.reserve', 'spaces.approve_reservation', 'spaces.cancel_reservation', 'spaces.manage_maintenance', 'spaces.manage_cleaning',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.move', 'inventory.loan', 'inventory.return', 'inventory.adjust', 'inventory.breakage', 'inventory.restock', 'inventory.approve_restock', 'inventory.manage_categories', 'inventory.manage_locations',
            'hr.view', 'hr.create', 'hr.update', 'hr.delete', 'hr.manage_departments', 'hr.manage_teams', 'hr.manage_schedules', 'hr.view_attendance', 'hr.create_attendance', 'hr.validate_attendance', 'hr.view_leave', 'hr.create_leave', 'hr.approve_leave', 'hr.reject_leave', 'hr.assign_employees',
            'planning.view', 'planning.create', 'planning.update', 'planning.approve', 'planning.cancel', 'planning.complete', 'planning.delete', 'planning.manage_tasks', 'planning.manage_resources', 'planning.manage_recurring', 'planning.execute_recurring',
            'reports.view', 'reports.export', 'reports.tickets', 'reports.tasks', 'reports.events', 'reports.spaces', 'reports.inventory', 'reports.hr', 'reports.planning', 'reports.documents',
        ]);

        Role::findByName('rh', 'web')->syncPermissions([
            'admin.access',
            'hr.view', 'hr.create', 'hr.update', 'hr.delete', 'hr.manage_departments', 'hr.manage_teams', 'hr.manage_schedules', 'hr.view_attendance', 'hr.create_attendance', 'hr.validate_attendance', 'hr.view_leave', 'hr.create_leave', 'hr.approve_leave', 'hr.reject_leave', 'hr.assign_employees',
            'planning.view',
            'reports.view', 'reports.hr',
        ]);

        Role::findByName('executivo', 'web')->syncPermissions([
            'admin.access',
            'tickets.view', 'tickets.create', 'tickets.assign', 'tickets.update', 'tickets.close',
            'tasks.view', 'tasks.create', 'tasks.assign', 'tasks.update', 'tasks.complete',
            'events.view', 'events.create', 'events.update',
            'documents.view', 'documents.download', 'documents.manage_access', 'documents.approve',
            'meeting_minutes.view', 'meeting_minutes.approve',
            'spaces.view', 'spaces.approve_reservation',
            'hr.view', 'hr.approve_leave',
            'planning.view', 'planning.approve', 'planning.cancel', 'planning.complete',
            'reports.view', 'reports.tickets', 'reports.tasks', 'reports.events', 'reports.spaces', 'reports.planning', 'reports.documents',
        ]);

        Role::findByName('administrativo', 'web')->syncPermissions([
            'admin.access',
            'contacts.view', 'contacts.create', 'contacts.update',
            'tickets.view', 'tickets.create', 'tickets.update',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.complete',
            'events.view', 'events.create', 'events.update',
            'documents.view', 'documents.upload', 'documents.update', 'documents.download',
            'meeting_minutes.view', 'meeting_minutes.create', 'meeting_minutes.update',
            'spaces.view', 'spaces.create', 'spaces.update', 'spaces.reserve', 'spaces.cancel_reservation',
            'inventory.view', 'inventory.loan', 'inventory.return', 'inventory.restock',
            'hr.view_attendance', 'hr.create_attendance', 'hr.view_leave', 'hr.create_leave',
            'planning.view', 'planning.create', 'planning.update',
            'reports.view', 'reports.tickets', 'reports.events', 'reports.documents',
        ]);

        Role::findByName('operacional', 'web')->syncPermissions([
            'admin.access',
            'tickets.view', 'tickets.update',
            'tasks.view', 'tasks.update', 'tasks.complete',
            'events.view',
            'documents.view',
            'meeting_minutes.view',
            'spaces.view', 'spaces.manage_maintenance',
            'inventory.view', 'inventory.move',
            'planning.view',
            'reports.view', 'reports.tasks', 'reports.events', 'reports.spaces', 'reports.planning',
        ]);

        Role::findByName('manutencao', 'web')->syncPermissions([
            'admin.access',
            'tickets.view', 'tickets.update',
            'tasks.view', 'tasks.update', 'tasks.complete',
            'events.view',
            'documents.view',
            'meeting_minutes.view',
            'spaces.view', 'spaces.manage_maintenance', 'spaces.manage_cleaning',
            'inventory.view', 'inventory.move', 'inventory.breakage',
            'planning.view',
            'reports.view', 'reports.tasks', 'reports.events', 'reports.spaces', 'reports.planning',
        ]);

        Role::findByName('armazem', 'web')->syncPermissions([
            'admin.access',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.move', 'inventory.loan', 'inventory.return', 'inventory.adjust', 'inventory.breakage', 'inventory.restock', 'inventory.approve_restock', 'inventory.manage_categories', 'inventory.manage_locations',
            'planning.view',
            'reports.view', 'reports.inventory',
        ]);

        Role::findByName('cidadao', 'web')->syncPermissions([
            'documents.view', 'documents.download',
            'meeting_minutes.view',
            'spaces.view', 'spaces.reserve',
        ]);

        Role::findByName('associacao', 'web')->syncPermissions([
            'documents.view', 'documents.download',
            'meeting_minutes.view',
            'spaces.view', 'spaces.reserve',
        ]);

        Role::findByName('empresa', 'web')->syncPermissions([
            'documents.view', 'documents.download',
            'meeting_minutes.view',
            'spaces.view', 'spaces.reserve',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}