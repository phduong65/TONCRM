<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'conversation_id', 'sender_type', 'sender_id',
        'message_type', 'content', 'payload', 'platform_message_id',
    ];

    protected $casts = ['payload' => 'array'];

    const SENDER_CUSTOMER = 'customer';
    const SENDER_STAFF    = 'staff';
    const SENDER_AI       = 'ai_agent';

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isFromCustomer(): bool
    {
        return $this->sender_type === self::SENDER_CUSTOMER;
    }

    public function isFromAi(): bool
    {
        return $this->sender_type === self::SENDER_AI;
    }

    public function isFromStaff(): bool
    {
        return $this->sender_type === self::SENDER_STAFF;
    }
}
