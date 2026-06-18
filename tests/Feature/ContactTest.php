<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tenant;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function test_admin_can_view_contacts(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)->get(route('contacts.index'))->assertOk();
    }

    public function test_staff_can_create_contact(): void
    {
        $staff = $this->createUserWithRole('staff');

        $response = $this->actingAs($staff)->post(route('contacts.store'), [
            'name'  => 'Nguyễn Thị Bình',
            'phone' => '0901234567',
            'email' => 'binh@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'name'      => 'Nguyễn Thị Bình',
            'tenant_id' => $staff->tenant_id,
        ]);
    }

    public function test_staff_can_update_contact(): void
    {
        $staff   = $this->createUserWithRole('staff');
        $contact = Contact::create([
            'tenant_id' => $staff->tenant_id,
            'name'      => 'Old Name',
            'phone'     => '0900000000',
        ]);

        $this->actingAs($staff)
            ->put(route('contacts.update', $contact), ['name' => 'New Name', 'phone' => '0900000000'])
            ->assertRedirect();

        $this->assertSame('New Name', $contact->fresh()->name);
    }

    public function test_staff_cannot_delete_contact(): void
    {
        $staff   = $this->createUserWithRole('staff');
        $contact = Contact::create(['tenant_id' => $staff->tenant_id, 'name' => 'To Delete']);

        $this->actingAs($staff)
            ->delete(route('contacts.destroy', $contact))
            ->assertForbidden();
    }

    public function test_admin_can_delete_contact(): void
    {
        $admin   = $this->createUserWithRole('admin');
        $contact = Contact::create(['tenant_id' => $admin->tenant_id, 'name' => 'To Delete']);

        $this->actingAs($admin)
            ->delete(route('contacts.destroy', $contact))
            ->assertRedirect();

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_tenant_isolation_on_contacts(): void
    {
        $tenant1 = $this->createTenant('T1');
        $admin1  = $this->createUserWithRole('admin', $tenant1);
        Contact::create(['tenant_id' => $tenant1->id, 'name' => 'T1 Contact']);

        $tenant2 = $this->createTenant('T2');
        $admin2  = $this->createUserWithRole('admin', $tenant2);

        $response = $this->actingAs($admin2)->get(route('contacts.index'));
        $response->assertOk()->assertDontSee('T1 Contact');
    }

    public function test_cannot_update_other_tenant_contact(): void
    {
        $tenant1 = $this->createTenant('T1');
        $admin1  = $this->createUserWithRole('admin', $tenant1);
        $contact = Contact::create(['tenant_id' => $tenant1->id, 'name' => 'T1 Contact']);

        $tenant2 = $this->createTenant('T2');
        $admin2  = $this->createUserWithRole('admin', $tenant2);

        $this->actingAs($admin2)
            ->put(route('contacts.update', $contact), ['name' => 'Hacked'])
            ->assertForbidden();
    }
}
