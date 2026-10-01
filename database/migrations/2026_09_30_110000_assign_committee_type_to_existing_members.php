<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Committee type is now required: put members saved without one into the Central Committee. */
    public function up(): void
    {
        if (! DB::table('committee_members')->whereNull('committee_type_id')->exists()) {
            return;
        }

        $centralId = DB::table('committee_types')->where('name_np', 'केन्द्रीय समिति')->value('id')
            ?? DB::table('committee_types')->insertGetId([
                'name_np' => 'केन्द्रीय समिति',
                'name_en' => 'Central Committee',
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('committee_members')->whereNull('committee_type_id')->update(['committee_type_id' => $centralId]);
    }

    public function down(): void
    {
        // Data only; nothing to undo.
    }
};
