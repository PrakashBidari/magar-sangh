<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The single row of sifaris letter settings: signatory, signature image and the footer contact details. */
class SifarisSetting extends Model
{
    /** What the letter starts with (see SifarisSettingSeeder). */
    public const DEFAULTS = [
        'signature_url' => '/images/sifaris-signature.png',
        'signatory_name' => 'होमराज खमारी मगर',
        'signatory_title' => 'महासचिव',
        'phone' => '01-5218294, 01-5218337, 9851064196, 9851016713',
        'email' => 'nepalmagarsangh@hotmail.com',
        'website' => 'magarsangh.org.np',
    ];

    protected $fillable = ['signature_url', 'signatory_name', 'signatory_title', 'phone', 'email', 'website'];

    public static function current(): self
    {
        return once(fn () => static::query()->firstOrCreate([], self::DEFAULTS));
    }

    /** Phone numbers one by one, so the letter never breaks a number across two lines. */
    public function phoneNumbers(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->phone))));
    }
}
