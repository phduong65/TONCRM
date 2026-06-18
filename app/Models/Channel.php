<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id', 'platform', 'platform_channel_id', 'name',
        'access_token', 'webhook_secret', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    const PLATFORMS = ['facebook', 'zalo', 'tiktok', 'webchat'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            'facebook' => 'Facebook',
            'zalo'     => 'Zalo OA',
            'tiktok'   => 'TikTok',
            'webchat'  => 'WebChat',
            default    => ucfirst($this->platform),
        };
    }

    public function getPlatformColorAttribute(): string
    {
        return match($this->platform) {
            'facebook' => 'bg-blue-100 text-blue-700',
            'zalo'     => 'bg-sky-100 text-sky-700',
            'tiktok'   => 'bg-gray-900 text-white',
            'webchat'  => 'bg-violet-100 text-violet-700',
            default    => 'bg-gray-100 text-gray-700',
        };
    }
}
