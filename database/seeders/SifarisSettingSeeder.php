<?php

namespace Database\Seeders;

use App\Models\SifarisSetting;
use Illuminate\Database\Seeder;

class SifarisSettingSeeder extends Seeder
{
    /** The one settings row; afterwards it is only edited from Dashboard > Sifaris > Sifaris Settings. */
    public function run(): void
    {
        SifarisSetting::query()->firstOrCreate([], SifarisSetting::DEFAULTS);
    }
}
