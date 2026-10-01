<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** A request for a recommendation letter (सिफारिस) confirming the applicant is Magar. */
class SifarisRequest extends Model
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    /** Relation to the parent / husband named on the letter. */
    public const PARENT_RELATIONS = ['छोरा' => 'Son (छोरा)', 'छोरी' => 'Daughter (छोरी)', 'श्रीमती' => 'Wife (श्रीमती)'];

    /** Relation to the grandparent / father-in-law named on the letter. */
    public const GRANDPARENT_RELATIONS = ['नाति' => 'Grandson (नाति)', 'नातिनी' => 'Granddaughter (नातिनी)', 'बुहारी' => 'Daughter-in-law (बुहारी)'];

    public const PROVINCES = ['कोशी', 'मधेश', 'बागमती', 'गण्डकी', 'लुम्बिनी', 'कर्णाली', 'सुदूरपश्चिम'];

    protected $fillable = [
        'user_id', 'status',
        'applicant_name', 'parent_relation', 'parent_name', 'grandparent_relation', 'grandparent_name',
        'province', 'district', 'municipality', 'ward_no', 'photo_url', 'mobile', 'email',
        'letter_number', 'dispatch_number', 'letter_date',
        'applied_at', 'approved_at', 'rejected_at', 'rejection_reason', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
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

    /** SIF-000123, for quoting the request to the office. */
    public function reference(): string
    {
        return 'SIF-'.str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::APPROVED => 'Approved',
            self::REJECTED => 'Disapproved',
            default => 'Pending',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            self::APPROVED => 'bg-green-100 text-green-700',
            self::REJECTED => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    /** @param array{letter_number?: ?string, dispatch_number?: ?string, letter_date?: ?string} $letter */
    public function approve(User $reviewer, array $letter = []): void
    {
        $this->forceFill($letter + [
            'status' => self::APPROVED,
            'approved_at' => now(),
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
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewer->getKey(),
        ])->save();
    }

    public function deletePhoto(): void
    {
        if ($this->photo_url && str_starts_with($this->photo_url, '/storage/')) {
            Storage::disk('public')->delete(Str::after($this->photo_url, '/storage/'));
        }
    }
}
