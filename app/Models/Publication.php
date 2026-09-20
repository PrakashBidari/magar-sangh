<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'file_url', 'published_date'];

    protected function casts(): array
    {
        return ['published_date' => 'date'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
