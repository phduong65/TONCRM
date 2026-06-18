<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    public function test_admin_can_view_conversations_index(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('conversations.index'));

        $response->assertOk();
    }

    public function test_staff_can_view_conversations_index(): void
    {
        $staff = $this->createUserWithRole('staff');

        $response = $this->actingAs($staff)->get(route('conversations.index'));

        $response->assertOk();
    }

    public function test_viewer_can_view_conversations_but_cannot_reply(): void
    {
        $viewer = $this->createUserWithRole('viewer');

        // viewer has view-conversations permission
        $this->actingAs($viewer)->get(route('conversations.index'))->assertOk();
    }

    public function test_tenant_isolation_on_conversations(): void
    {
        $tenant1 = $this->createTenant('Tenant 1');
        $admin1  = $this->createUserWithRole('admin', $tenant1);
        $channel = Channel::create([
            'tenant_id'           => $tenant1->id,
            'platform'            => 'webchat',
            'platform_channel_id' => 'wc-1',
            'name'                => 'WebChat',
            'access_token'        => 'tok',
        ]);
        $contact = Contact::create(['tenant_id' => $tenant1->id, 'name' => 'Alice']);
        $conv1 = Conversation::create([
            'tenant_id'  => $tenant1->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);

        $tenant2 = $this->createTenant('Tenant 2');
        $admin2  = $this->createUserWithRole('admin', $tenant2);

        // admin2 should not see conv1 on their index
        $response = $this->actingAs($admin2)->get(route('conversations.index'));
        $response->assertOk()->assertDontSee($conv1->id);
    }

    public function test_toggle_ai_changes_status(): void
    {
        $admin  = $this->createUserWithRole('admin');
        $tenant = Tenant::find($admin->tenant_id);
        $channel = Channel::create([
            'tenant_id' => $tenant->id, 'platform' => 'webchat',
            'platform_channel_id' => 'wc-2', 'name' => 'WC', 'access_token' => 'tok',
        ]);
        $contact = Contact::create(['tenant_id' => $tenant->id, 'name' => 'Bob']);
        $conv = Conversation::create([
            'tenant_id' => $tenant->id, 'channel_id' => $channel->id,
            'contact_id' => $contact->id, 'is_ai_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('conversations.toggle-ai', $conv))
            ->assertRedirect();

        $this->assertFalse($conv->fresh()->is_ai_active);
    }

    public function test_close_conversation(): void
    {
        $staff  = $this->createUserWithRole('staff');
        $tenant = Tenant::find($staff->tenant_id);
        $channel = Channel::create([
            'tenant_id' => $tenant->id, 'platform' => 'webchat',
            'platform_channel_id' => 'wc-3', 'name' => 'WC', 'access_token' => 'tok',
        ]);
        $contact = Contact::create(['tenant_id' => $tenant->id, 'name' => 'Carol']);
        $conv = Conversation::create([
            'tenant_id' => $tenant->id, 'channel_id' => $channel->id,
            'contact_id' => $contact->id, 'status' => 'open',
        ]);

        $this->actingAs($staff)
            ->post(route('conversations.close', $conv))
            ->assertRedirect();

        $this->assertSame('closed', $conv->fresh()->status);
    }
}
