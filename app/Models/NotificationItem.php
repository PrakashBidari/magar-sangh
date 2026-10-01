<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NotificationItem extends Model
{
    use HasFactory;

    /** At most this many popups are offered to one page (newest first, shown one after another). */
    public const MAX_POPUPS = 3;

    protected $table = 'notifications_list';

    protected $fillable = [
        'title', 'slug', 'body', 'published_at', 'image_url',
        'show_popup', 'popup_pages', 'popup_frequency', 'popup_on_exit', 'popup_repeat_minutes',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'show_popup' => 'boolean',
            'popup_on_exit' => 'boolean',
            'popup_repeat_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Published notifications marked as popups, for the home page or for every other public page. */
    public function scopePopupsFor(Builder $query, bool $onHomePage): void
    {
        $query->where('show_popup', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->when(! $onHomePage, fn (Builder $q) => $q->where('popup_pages', 'all'))
            ->orderByDesc('published_at')
            ->limit(self::MAX_POPUPS);
    }

    /**
     * What the popup script needs (see resources/views/partials/notification-popup.blade.php).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function popupPayload(bool $onHomePage): Collection
    {
        return static::query()->popupsFor($onHomePage)->get()->map(fn (self $n) => [
            'id' => $n->id,
            'title' => $n->title,
            'text' => Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $n->body)))), 220),
            'image' => $n->image_url,
            'url' => route('media.notifications.show', $n),
            'always' => $n->popup_frequency === 'always',
            'exit' => $n->popup_on_exit,
            'repeat' => (int) $n->popup_repeat_minutes,
        ])->toBase();
    }
}
