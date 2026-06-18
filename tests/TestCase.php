<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
    }

    protected function createTenant(string $name = 'Test Tenant'): Tenant
    {
        return Tenant::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'plan' => 'starter',
        ]);
    }

    protected function createUserWithRole(string $role, ?Tenant $tenant = null): User
    {
        $tenant ??= $this->createTenant();

        $user = User::create([
            'name'      => ucfirst($role) . ' User',
            'email'     => $role . '-' . Str::random(6) . '@test.com',
            'password'  => bcrypt('password'),
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }
}
