<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name_np', 'site_name_en', 'tagline_np', 'tagline_en',
        'logo_url', 'flag_url', 'phone', 'email', 'address_np', 'address_en',
        'map_embed_url', 'facebook_url', 'instagram_url', 'youtube_url',
        'twitter_url', 'tiktok_url', 'footer_credit',
        'history_content', 'mission_vision_content', 'constitution_content',
        'about_short_np', 'about_short_en',
        'president_message_np', 'president_name_np', 'president_photo_url',
        'stat_members', 'stat_districts', 'stat_countries', 'stat_sister_orgs',
    ];

    public static function current(): self
    {
        return once(fn () => static::query()->firstOrCreate([]));
    }
}
