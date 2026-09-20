<?php

namespace Database\Seeders;

use App\Models\SisterOrganization;
use Illuminate\Database\Seeder;

class SisterOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = [
            'Nepal Magar Women Association',
            'Nepal Magar Student Association',
            'Nepal Magar Youth Association',
            'Nepal Magar Cultural Association',
            'Nepal Magar Contact Coordination Association',
            'Nepal Magar Ex-Army Association',
        ];

        foreach ($orgs as $i => $name) {
            SisterOrganization::factory()->create([
                'name_en' => $name,
                'sort_order' => $i,
            ]);
        }
    }
}
