<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'photo_url', 'position_np', 'position_en',
        'term_label', 'term_start', 'term_end',
        'is_past_president', 'is_current', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'term_start' => 'date',
            'term_end' => 'date',
            'is_past_president' => 'boolean',
            'is_current' => 'boolean',
        ];
    }
}
