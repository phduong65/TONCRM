<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\KnowledgeBase;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TonCrmSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ───────────────────────────────────────────────
        $permissions = [
            'view-conversations', 'reply-conversations', 'assign-conversations',
            'view-contacts', 'create-contacts', 'edit-contacts', 'delete-contacts',
            'view-channels', 'create-channels', 'edit-channels', 'delete-channels',
            'view-knowledge-bases', 'create-knowledge-bases', 'edit-knowledge-bases', 'delete-knowledge-bases',
            'manage-ai-settings',
            'view-reports', 'export-reports',
            'manage-settings', 'manage-users', 'manage-roles',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view-conversations', 'reply-conversations', 'assign-conversations',
            'view-contacts', 'create-contacts', 'edit-contacts',
            'view-channels', 'create-channels', 'edit-channels',
            'view-knowledge-bases', 'create-knowledge-bases', 'edit-knowledge-bases', 'delete-knowledge-bases',
            'view-reports', 'export-reports',
        ]);

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'view-conversations', 'reply-conversations',
            'view-contacts', 'create-contacts', 'edit-contacts',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(['view-reports', 'view-conversations']);

        // ── Tenant ────────────────────────────────────────────────────
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demo Company', 'plan' => 'pro', 'is_active' => true]
        );

        // ── Users ─────────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            ['name' => 'Admin Demo', 'password' => Hash::make('password'), 'tenant_id' => $tenant->id]
        );
        $adminUser->assignRole('admin');

        $staffUser = User::firstOrCreate(
            ['email' => 'staff@demo.com'],
            ['name' => 'Nguyễn Minh Tuấn', 'password' => Hash::make('password'), 'tenant_id' => $tenant->id]
        );
        $staffUser->assignRole('staff');

        $staffUser2 = User::firstOrCreate(
            ['email' => 'staff2@demo.com'],
            ['name' => 'Lê Thị Hương', 'password' => Hash::make('password'), 'tenant_id' => $tenant->id]
        );
        $staffUser2->assignRole('staff');

        // ── Channels ──────────────────────────────────────────────────
        $webchat = Channel::firstOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => 'webchat', 'platform_channel_id' => 'demo-webchat'],
            ['name' => 'WebChat Demo', 'access_token' => 'demo-token', 'is_active' => true]
        );

        $facebook = Channel::firstOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => 'facebook', 'platform_channel_id' => 'demo-fb-page'],
            ['name' => 'Facebook Fanpage Demo', 'access_token' => 'demo-fb-token', 'webhook_secret' => 'demo-fb-secret', 'is_active' => true]
        );

        $zalo = Channel::firstOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => 'zalo', 'platform_channel_id' => 'demo-zalo-oa'],
            ['name' => 'Zalo OA Demo', 'access_token' => 'demo-zalo-token', 'is_active' => true]
        );

        // ── Contacts ──────────────────────────────────────────────────
        $contactA = Contact::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '0901234567'],
            ['name' => 'Nguyễn Văn An', 'email' => 'nguyenvanan@gmail.com', 'platform_ids' => ['webchat' => 'wc-001']]
        );

        $contactB = Contact::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '0912345678'],
            ['name' => 'Trần Thị Bình', 'platform_ids' => ['facebook' => 'fb-psid-001']]
        );

        $contactC = Contact::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '0923456789'],
            ['name' => 'Lê Minh Châu', 'email' => 'leminhchau@gmail.com', 'platform_ids' => ['zalo' => 'zalo-uid-001']]
        );

        $contactD = Contact::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '0934567890'],
            ['name' => 'Phạm Thị Dung', 'platform_ids' => ['facebook' => 'fb-psid-002']]
        );

        $contactE = Contact::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '0945678901'],
            ['name' => 'Hoàng Văn Em', 'platform_ids' => ['webchat' => 'wc-002']]
        );

        $contactF = Contact::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '0956789012'],
            ['name' => 'Vũ Thị Phương', 'platform_ids' => ['zalo' => 'zalo-uid-002']]
        );

        // ── Conversations + Messages ───────────────────────────────────

        // Conv 1 — WebChat, open, AI active, recent
        $this->makeConv($tenant, $webchat, $contactA, [
            'status'          => 'open',
            'is_ai_active'    => true,
            'assigned_user_id'=> null,
            'last_message_at' => now()->subMinutes(3),
        ], [
            ['customer', 'Xin chào! Tôi muốn hỏi về gói dịch vụ Pro của bạn.',         -18],
            ['ai_agent', 'Xin chào Anh/Chị! Gói Pro của chúng tôi có giá 500.000đ/tháng, hỗ trợ không giới hạn hội thoại và đầy đủ tính năng AI. Anh/Chị có muốn dùng thử miễn phí 14 ngày không?', -15],
            ['customer', 'Gói Pro có hỗ trợ Zalo không?',                                -10],
            ['ai_agent', 'Dạ có! Gói Pro hỗ trợ Facebook Messenger, Zalo OA, TikTok và WebChat trong một hộp thư duy nhất. AI tự động phản hồi 24/7 dựa trên Knowledge Base của doanh nghiệp.', -8],
            ['customer', 'Cho tôi xem demo được không?',                                 -3],
        ]);

        // Conv 2 — Facebook, open, assigned to staff, unread
        $this->makeConv($tenant, $facebook, $contactB, [
            'status'          => 'open',
            'is_ai_active'    => false,
            'assigned_user_id'=> $staffUser->id,
            'last_message_at' => now()->subMinutes(12),
        ], [
            ['customer', 'Alo, tôi đặt hàng ngày hôm qua mà chưa thấy xác nhận?',      -45],
            ['staff',    'Chào chị Bình! Để em kiểm tra đơn hàng cho chị nhé.',         -40],
            ['customer', 'Mã đơn của tôi là DH2024-001',                                -38],
            ['staff',    'Vâng em thấy rồi ạ! Đơn hàng của chị đang được chuẩn bị, dự kiến giao ngày mai trước 12h trưa ạ.', -35],
            ['customer', 'OK cảm ơn em. À mà tôi muốn đổi địa chỉ giao hàng được không?', -12],
        ]);

        // Conv 3 — Zalo OA, pending, unassigned
        $this->makeConv($tenant, $zalo, $contactC, [
            'status'          => 'pending',
            'is_ai_active'    => true,
            'assigned_user_id'=> null,
            'last_message_at' => now()->subMinutes(30),
        ], [
            ['customer', 'Tôi mua sản phẩm bị lỗi, muốn đổi trả.',                     -35],
            ['ai_agent', 'Chào Anh/Chị! Rất tiếc khi nghe vậy. Để được hỗ trợ đổi trả, Anh/Chị vui lòng cung cấp: 1. Mã đơn hàng, 2. Ảnh chụp sản phẩm lỗi. Chúng tôi sẽ xử lý trong vòng 24h!', -30],
            ['customer', 'Đây là ảnh: [hình ảnh]',                                      -28],
            ['customer', 'Mã đơn là DH2024-088',                                        -28],
            ['ai_agent', 'Cảm ơn Anh/Chị đã cung cấp thông tin. Bộ phận kỹ thuật đang xem xét. Nhân viên sẽ liên hệ lại trong 2 tiếng!', -27],
            ['customer', 'Đã 1 tiếng rồi mà chưa thấy ai liên hệ??',                   -30],
        ]);

        // Conv 4 — Facebook, open, AI active, 1 giờ trước
        $this->makeConv($tenant, $facebook, $contactD, [
            'status'          => 'open',
            'is_ai_active'    => true,
            'assigned_user_id'=> $staffUser2->id,
            'last_message_at' => now()->subHour(),
        ], [
            ['customer', 'Bạn có ship tỉnh không?',                                     -70],
            ['ai_agent', 'Dạ có ạ! Chúng tôi ship toàn quốc. Phí ship tùy theo tỉnh thành, thông thường 30.000–50.000đ. Anh/Chị ở tỉnh nào để em báo phí chính xác ạ?', -65],
            ['customer', 'Tôi ở Đà Nẵng',                                               -62],
            ['ai_agent', 'Ship Đà Nẵng 35.000đ, thời gian 2-3 ngày làm việc ạ. Anh/Chị muốn đặt hàng ngay không?', -60],
        ]);

        // Conv 5 — WebChat, closed
        $this->makeConv($tenant, $webchat, $contactE, [
            'status'          => 'closed',
            'is_ai_active'    => false,
            'assigned_user_id'=> $staffUser->id,
            'last_message_at' => now()->subHours(3),
        ], [
            ['customer', 'Tôi cần hủy đơn hàng DH2024-055',    -200],
            ['staff',    'Vâng chị, em đã hủy đơn cho chị rồi ạ. Tiền hoàn trong 3-5 ngày làm việc.', -190],
            ['customer', 'Cảm ơn em nhiều!',                    -185],
            ['staff',    'Dạ không có gì ạ! Chúc chị buổi chiều vui vẻ.',               -183],
        ]);

        // Conv 6 — Zalo, open, mới nhất
        $this->makeConv($tenant, $zalo, $contactF, [
            'status'          => 'open',
            'is_ai_active'    => true,
            'assigned_user_id'=> null,
            'last_message_at' => now()->subMinutes(1),
        ], [
            ['customer', 'Cho hỏi sản phẩm còn hàng không ạ?',   -5],
            ['ai_agent', 'Dạ còn hàng ạ! Sản phẩm hiện có sẵn kho, đặt hàng trước 15h giao trong ngày ạ.', -3],
            ['customer', 'Giá bao nhiêu vậy?',                    -1],
        ]);

        // ── Knowledge Base ─────────────────────────────────────────────
        KnowledgeBase::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Giới thiệu sản phẩm và bảng giá'],
            [
                'content'     => "Chúng tôi cung cấp giải pháp CRM đa kênh tích hợp AI.\n\nBảng giá:\n- Gói Starter: Miễn phí, 1 kênh, 100 hội thoại/tháng\n- Gói Pro: 500.000đ/tháng, không giới hạn kênh và hội thoại, AI Agent\n- Gói Enterprise: Liên hệ báo giá, on-premise, SLA 99.9%\n\nTất cả gói đều hỗ trợ Facebook Messenger, Zalo OA, TikTok và WebChat.",
                'source_type' => 'manual',
            ]
        );

        KnowledgeBase::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Chính sách vận chuyển'],
            [
                'content'     => "Phí vận chuyển:\n- Hà Nội, TP.HCM: 25.000đ, giao trong ngày nếu đặt trước 15h\n- Đà Nẵng, Cần Thơ: 35.000đ, 1-2 ngày làm việc\n- Các tỉnh khác: 40.000-50.000đ, 2-4 ngày làm việc\n\nMinh phí ship cho đơn từ 500.000đ.",
                'source_type' => 'manual',
            ]
        );

        KnowledgeBase::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Chính sách đổi trả và hoàn tiền'],
            [
                'content'     => "Đổi trả trong vòng 30 ngày kể từ ngày nhận hàng.\n\nĐiều kiện:\n- Sản phẩm còn nguyên tem, hộp\n- Có hóa đơn mua hàng\n- Không áp dụng với hàng khuyến mãi\n\nHoàn tiền: 3-5 ngày làm việc qua tài khoản ngân hàng.\nLiên hệ: support@toncrm.vn hoặc 1800-xxxx (miễn phí).",
                'source_type' => 'manual',
            ]
        );

        KnowledgeBase::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Hướng dẫn sử dụng sản phẩm'],
            [
                'content'     => "Hướng dẫn cơ bản:\n1. Đăng nhập tại app.toncrm.vn\n2. Kết nối kênh: Vào Channels > Thêm kênh\n3. Cài đặt AI: Upload nội dung vào Knowledge Base\n4. Bắt đầu nhận tin nhắn tự động\n\nHỗ trợ kỹ thuật: Thứ 2-6, 8h-17h30. Liên hệ qua chat hoặc email.",
                'source_type' => 'manual',
            ]
        );

        $this->command->info('Seeder hoàn tất!');
        $this->command->info('Tài khoản demo:');
        $this->command->info('  admin@demo.com / password  (Admin)');
        $this->command->info('  staff@demo.com / password  (Staff)');
        $this->command->info('  staff2@demo.com / password (Staff)');
    }

    private function makeConv(
        $tenant, $channel, $contact,
        array $attrs,
        array $messages
    ): Conversation {
        $conv = Conversation::firstOrCreate(
            ['channel_id' => $channel->id, 'contact_id' => $contact->id],
            array_merge(['tenant_id' => $tenant->id], $attrs)
        );

        if ($conv->messages()->count() === 0) {
            foreach ($messages as [$senderType, $content, $minutesAgo]) {
                $msg = Message::create([
                    'conversation_id' => $conv->id,
                    'sender_type'     => $senderType,
                    'sender_id'       => $senderType === 'customer' ? ($contact->platform_ids[$channel->platform] ?? 'unknown') : ($senderType === 'ai_agent' ? 'ai' : 'staff'),
                    'message_type'    => 'text',
                    'content'         => $content,
                ]);
                $msg->created_at = Carbon::now()->addMinutes($minutesAgo);
                $msg->save();
            }
        }

        return $conv;
    }
}
