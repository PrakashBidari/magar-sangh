<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = ['donor_name', 'donor_image_url', 'amount', 'address', 'donate_date'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'donate_date' => 'date',
        ];
    }

    public function displayImage(): string
    {
        return $this->donor_image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->donor_name) . '&background=001F5B&color=fff&size=128';
    }
}
