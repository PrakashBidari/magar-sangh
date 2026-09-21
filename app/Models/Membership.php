<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Membership extends Model
{
    use HasFactory;

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'user_id', 'membership_type_id', 'membership_number', 'status',
        'full_name', 'surname', 'date_of_birth', 'permanent_address', 'current_address',
        'province', 'district', 'municipality', 'ward_no', 'mobile', 'email', 'occupation',
        'photo_url', 'signature_url', 'voucher_path',
        'applied_at', 'approved_at', 'expires_at', 'rejected_at', 'rejection_reason', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'applied_at' => 'date',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Approved and not yet expired. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::APPROVED)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->isApproved() && $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->isApproved() && ! $this->isExpired();
    }

    public function displayName(): string
    {
        return trim($this->full_name.' '.$this->surname);
    }

    /** Label for badges: Pending / Active / Expired / Disapproved. */
    public function statusLabel(): string
    {
        return match (true) {
            $this->isPending() => 'Pending',
            $this->isRejected() => 'Disapproved',
            $this->isExpired() => 'Expired',
            default => 'Active',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->statusLabel()) {
            'Pending' => 'bg-amber-100 text-amber-700',
            'Disapproved' => 'bg-red-100 text-red-700',
            'Expired' => 'bg-gray-200 text-gray-600',
            default => 'bg-green-100 text-green-700',
        };
    }

    /** NMS-000123. Derived from the auto-increment id, so a number can never be handed out twice. */
    public static function numberFor(self $membership): string
    {
        return 'NMS-'.str_pad((string) $membership->getKey(), 6, '0', STR_PAD_LEFT);
    }

    public function approve(User $reviewer): void
    {
        $now = now();

        $this->forceFill([
            'status' => self::APPROVED,
            'membership_number' => $this->membership_number ?: self::numberFor($this),
            'approved_at' => $now,
            'expires_at' => $this->type->expiryFrom($now),
            'rejected_at' => null,
            'rejection_reason' => null,
            'reviewed_by' => $reviewer->getKey(),
        ])->save();
    }

    public function reject(User $reviewer, ?string $reason = null): void
    {
        $this->forceFill([
            'status' => self::REJECTED,
            'approved_at' => null,
            'expires_at' => null,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewer->getKey(),
        ])->save();
    }

    /** Remove the uploaded files (photo and signature are public, the voucher is private). */
    public function deleteFiles(): void
    {
        foreach ([$this->photo_url, $this->signature_url] as $url) {
            if ($url && str_starts_with($url, '/storage/')) {
                Storage::disk('public')->delete(Str::after($url, '/storage/'));
            }
        }

        if ($this->voucher_path) {
            Storage::disk('local')->delete($this->voucher_path);
        }
    }
}
