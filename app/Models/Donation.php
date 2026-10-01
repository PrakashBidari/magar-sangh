<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A donation. Members add their own from the dashboard; an admin approves it or
 * returns it with a note, the member fixes and resubmits, and it is approved.
 * Only approved donations are shown on the site and counted in totals.
 */
class Donation extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const RETURNED = 'returned';

    protected $fillable = ['user_id', 'donor_name', 'donor_image_url', 'voucher_url', 'amount', 'address', 'donate_date', 'status', 'review_note', 'reviewed_by', 'reviewed_at'];

    protected $attributes = ['status' => self::PENDING];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'donate_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', self::APPROVED);
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function isReturned(): bool
    {
        return $this->status === self::RETURNED;
    }

    public function approve(User $by): void
    {
        $this->update(['status' => self::APPROVED, 'review_note' => null, 'reviewed_by' => $by->id, 'reviewed_at' => now()]);
    }

    /** Send the donation back to the member to correct. */
    public function reject(User $by, ?string $note = null): void
    {
        $this->update(['status' => self::RETURNED, 'review_note' => $note, 'reviewed_by' => $by->id, 'reviewed_at' => now()]);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::APPROVED => 'Approved',
            self::RETURNED => 'Returned',
            default => 'Pending',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            self::APPROVED => 'bg-green-100 text-green-700',
            self::RETURNED => 'bg-orange-100 text-orange-700',
            default => 'bg-gold-100 text-gold-700',
        };
    }

    public function displayImage(): string
    {
        return $this->donor_image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->donor_name) . '&background=001F5B&color=fff&size=128';
    }
}
