<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::findOrCreate('super admin');
        $employeeRole = Role::findOrCreate('admin');

        $permissions = [
            'view-activities',
            'create-activities',
            'edit-activities',
            'delete-activities',
            // Templates
            'view-templates',
            'create-templates',
            'edit-templates',
            'delete-templates',

            // Customers
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // Brokers
            'view-brokers',
            'create-brokers',
            'edit-brokers',
            'delete-brokers',

            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Road Signs
            'view-road-signs',
            'create-road-signs',
            'edit-road-signs',
            'delete-road-signs',

            // Orders
            'view-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',

            // Payments
            'view-payments',
            'create-payments',
            'edit-payments',
            'delete-payments',

            // Bookings
            'view-bookings',
            'create-bookings',
            'edit-bookings',
            'delete-bookings',

            // Cities
            'view-cities',
            'create-cities',
            'edit-cities',
            'delete-cities',

            // Regions
            'view-regions',
            'create-regions',
            'edit-regions',
            'delete-regions',

            // Users
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-financials'
        ];


        $permissions_employee = [
            // Templates
            'view-templates',
            'create-templates',
            'edit-templates',

            // Customers
            'view-customers',
            'create-customers',
            'edit-customers',

            // Brokers
            'view-brokers',
            'create-brokers',
            'edit-brokers',

            // Products
            'view-products',
            'create-products',
            'edit-products',

            // Road Signs
            'view-road-signs',
            'create-road-signs',
            'edit-road-signs',

            // Orders
            'view-orders',
            'create-orders',
            'edit-orders',

            // Payments
            'view-payments',
            'create-payments',
            'edit-payments',

            // Bookings
            'view-bookings',
            'create-bookings',

            // Cities
            'view-cities',
            'create-cities',
            'edit-cities',

            // Regions
            'view-regions',
            'create-regions',
            'edit-regions',
        ];


        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
            $adminRole->givePermissionTo($permission);
        }

        foreach ($permissions_employee as $permission) {
            Permission::findOrCreate($permission);
            $employeeRole->givePermissionTo($permission);
        }

        //khader
        $employeeRole = Role::create([
            'name' => 'customer',
            'guard_name' => 'customer'
        ]);
        Permission::create(['name' => 'user']);
    }
}
