<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'image_url', 'published_at', 'status', 'reviewed_by', 'reviewed_at'];

    protected $attributes = ['status' => self::PENDING];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'reviewed_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Only approved news is shown on the public site. */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', self::APPROVED);
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function approve(User $by): void
    {
        $this->update(['status' => self::APPROVED, 'reviewed_by' => $by->id, 'reviewed_at' => now()]);
    }

    public function reject(User $by, ?string $note = null): void
    {
        $this->update(['status' => self::REJECTED, 'reviewed_by' => $by->id, 'reviewed_at' => now()]);
    }
}
