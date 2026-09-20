<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'image_url', 'location', 'event_date', 'event_time'];

    protected function casts(): array
    {
        return ['event_date' => 'date'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
