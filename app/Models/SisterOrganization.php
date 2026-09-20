<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisterOrganization extends Model
{
    use HasFactory;

    protected $fillable = ['name_np', 'name_en', 'logo_url', 'blurb', 'link', 'sort_order'];
}
