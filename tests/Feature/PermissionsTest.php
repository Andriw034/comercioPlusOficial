<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles for testing
        $permissions = [
            'products.view','products.create','products.update','products.delete',
            'categories.view','categories.create','categories.update','categories.delete',
            'stores.view','stores.create','stores.update','stores.delete',
            'orders.view','orders.create','orders.update','orders.delete',
            'carts.view','carts.create','carts.update','carts.delete',
            'users.view','users.create','users.update','users.delete',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $roleMap = [
            'admin' => $permissions,
            'cliente' => [
                'products.view','categories.view','stores.view',
                'orders.view','orders.create',
                'carts.view','carts.create','carts.update',
            ],
        ];

        foreach ($roleMap as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }

    public function test_permissions_table_exists_and_has_data()
    {
        $this->assertDatabaseHas('permissions', [
            'name' => 'products.view',
            'guard_name' => 'web'
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'users.create',
            'guard_name' => 'web'
        ]);
    }

    public function test_roles_table_exists_and_has_data()
    {
        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'cliente',
            'guard_name' => 'web'
        ]);
    }

    public function test_role_has_permissions()
    {
        $role = Role::where('name', 'admin')->first();
        $this->assertNotNull($role);

        $permissions = $role->permissions;
        $this->assertGreaterThan(0, $permissions->count());

        $this->assertTrue($role->hasPermissionTo('products.view'));
        $this->assertTrue($role->hasPermissionTo('users.create'));
    }

    public function test_user_can_be_assigned_role()
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'cliente')->first();

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('cliente'));
        $this->assertTrue($user->hasPermissionTo('products.view'));
    }
}
