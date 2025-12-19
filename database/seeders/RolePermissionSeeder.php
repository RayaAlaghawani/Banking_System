<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // create Role
        $adminRole = Role::create(['name' => 'admin']);
        $employeeRole = Role::create(['name' => 'teller']);
        $clientRole = Role::create(['name' => 'client']);
        $directorRole = Role::create(['name' => 'director']);

        // create Permission
        $permissions = [
            'login',
            'view_complaint',
            'edit_complaint',
            'add_note',
         //   'submit_complaint',
            'add_employee'
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // 2. إنشاء الصلاحيات الجديدة
    //    $submitComplaintPermission = Permission::firstOrCreate(['name' => 'submit complaint']);

        //$clientRole->givePermissionTo('submit_complaint');

        $adminRole->givePermissionTo($permissions);
        $employeeRole->givePermissionTo($permissions);

        // create Admin
        $adminUser = User::factory()->create([
            'email' => 'rahafAlghalaini1234@gmail.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        $adminUser->assignRole($adminRole);

// مزامنة الصلاحيات باستخدام أسماء الصلاحيات فقط
        $permissionNames = $adminRole->permissions->pluck('name')->toArray();
        $adminUser->syncPermissions($permissionNames);

    }
}
