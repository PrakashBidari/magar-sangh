<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MembershipType extends Model
{
    use HasFactory;

    public const LIFETIME = 'lifetime';

    protected $fillable = ['name_np', 'name_en', 'description', 'duration_unit', 'duration_value', 'fee', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'fee' => 'decimal:2'];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function getDurationLabelAttribute(): string
    {
        return $this->durationLabel();
    }

    public function getFeeLabelAttribute(): string
    {
        return $this->requiresPayment() ? 'Rs. '.number_format((float) $this->fee, 2) : 'Free';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function isLifetime(): bool
    {
        return $this->duration_unit === self::LIFETIME || ! $this->duration_value;
    }

    public function requiresPayment(): bool
    {
        return (float) $this->fee > 0;
    }

    /** When a membership approved at $from stops being valid; null means it never expires. */
    public function expiryFrom(\DateTimeInterface $from): ?Carbon
    {
        if ($this->isLifetime()) {
            return null;
        }

        $date = Carbon::instance($from);

        return $this->duration_unit === 'months'
            ? $date->addMonthsNoOverflow($this->duration_value)
            : $date->addYearsNoOverflow($this->duration_value);
    }

    /** "5 years", "6 months" or "Lifetime". */
    public function durationLabel(): string
    {
        if ($this->isLifetime()) {
            return 'Lifetime';
        }

        $unit = $this->duration_unit === 'months' ? 'month' : 'year';

        return $this->duration_value.' '.Str::plural($unit, $this->duration_value);
    }
}
