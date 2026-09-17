<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Projects
            'project.view',
            'project.create',
            'project.update',
            'project.delete',
            'project.manage-users',

            // Contractors
            'contractor.view',
            'contractor.create',
            'contractor.update',
            'contractor.delete',
            'contractor.assign',

            // Contractor Payments
            'contractor-payment.view',
            'contractor-payment.create',
            'contractor-payment.void',

            // Expenses
            'expense.view',
            'expense.create',
            'expense.update',
            'expense.delete',

            // Expense Categories
            'expense-category.view',
            'expense-category.create',
            'expense-category.update',
            'expense-category.delete',

            // Workers
            'worker.view',
            'worker.create',
            'worker.update',
            'worker.delete',

            // Attendance
            'attendance.view',
            'attendance.create',
            'attendance.update',

            // Reports
            'report.view',

            // Users
            'user.view',
            'user.create',
            'user.update',
            'user.delete',

            // Settings
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin — full access
        $adminRole = Role::firstOrCreate(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Owner — full project/financial access but no user management
        $ownerRole = Role::firstOrCreate(['name' => UserRole::Owner->value, 'guard_name' => 'web']);
        $ownerRole->syncPermissions([
            'dashboard.view',
            'project.view', 'project.create', 'project.update', 'project.manage-users',
            'contractor.view', 'contractor.create', 'contractor.update', 'contractor.assign',
            'contractor-payment.view', 'contractor-payment.create', 'contractor-payment.void',
            'expense.view', 'expense.create', 'expense.update', 'expense.delete',
            'expense-category.view',
            'worker.view',
            'attendance.view',
            'report.view',
        ]);

        // Contractor — worker and attendance management only
        $contractorRole = Role::firstOrCreate(['name' => UserRole::Contractor->value, 'guard_name' => 'web']);
        $contractorRole->syncPermissions([
            'dashboard.view',
            'project.view',
            'contractor.view',
            'contractor-payment.view',
            'expense.view',
            'expense-category.view',
            'worker.view', 'worker.create', 'worker.update',
            'attendance.view', 'attendance.create', 'attendance.update',
        ]);
    }
}
