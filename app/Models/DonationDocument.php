<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A titled PDF shown on the Lakhan Thapa Pratisthan page. */
class DonationDocument extends Model
{
    protected $fillable = ['title', 'file_url'];
}
