<?php

namespace Tests\Feature;

use App\Models\KnowledgeBase;
use App\Models\Tenant;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    public function test_admin_can_view_knowledge_bases(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)->get(route('knowledge-bases.index'))->assertOk();
    }

    public function test_manager_can_create_knowledge_base(): void
    {
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($manager)->post(route('knowledge-bases.store'), [
            'title'       => 'Chính sách đổi trả',
            'content'     => 'Sản phẩm được đổi trả trong vòng 7 ngày nếu còn nguyên đai nguyên kiện.',
            'source_type' => 'manual',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('knowledge_bases', [
            'title'     => 'Chính sách đổi trả',
            'tenant_id' => $manager->tenant_id,
        ]);
    }

    public function test_viewer_cannot_create_knowledge_base(): void
    {
        $viewer = $this->createUserWithRole('viewer');

        $this->actingAs($viewer)->post(route('knowledge-bases.store'), [
            'content' => 'Some content',
        ])->assertForbidden();
    }

    public function test_staff_cannot_create_knowledge_base(): void
    {
        $staff = $this->createUserWithRole('staff');

        $this->actingAs($staff)->post(route('knowledge-bases.store'), [
            'content' => 'Some content',
        ])->assertForbidden();
    }

    public function test_admin_can_delete_knowledge_base(): void
    {
        $admin = $this->createUserWithRole('admin');
        $kb    = KnowledgeBase::create([
            'tenant_id'   => $admin->tenant_id,
            'content'     => 'Test content',
            'source_type' => 'manual',
        ]);

        $this->actingAs($admin)
            ->delete(route('knowledge-bases.destroy', $kb))
            ->assertRedirect();

        $this->assertDatabaseMissing('knowledge_bases', ['id' => $kb->id]);
    }

    public function test_tenant_isolation_on_knowledge_bases(): void
    {
        $tenant1 = $this->createTenant('KB T1');
        $admin1  = $this->createUserWithRole('admin', $tenant1);
        KnowledgeBase::create([
            'tenant_id' => $tenant1->id,
            'title'     => 'T1 Private KB',
            'content'   => 'Secret content',
        ]);

        $tenant2 = $this->createTenant('KB T2');
        $admin2  = $this->createUserWithRole('admin', $tenant2);

        $response = $this->actingAs($admin2)->get(route('knowledge-bases.index'));
        $response->assertOk()->assertDontSee('T1 Private KB');
    }

    public function test_cannot_delete_other_tenant_knowledge_base(): void
    {
        $tenant1 = $this->createTenant('KB T1');
        $admin1  = $this->createUserWithRole('admin', $tenant1);
        $kb      = KnowledgeBase::create([
            'tenant_id' => $tenant1->id,
            'content'   => 'Secret',
        ]);

        $tenant2 = $this->createTenant('KB T2');
        $admin2  = $this->createUserWithRole('admin', $tenant2);

        $this->actingAs($admin2)
            ->delete(route('knowledge-bases.destroy', $kb))
            ->assertForbidden();
    }
}
