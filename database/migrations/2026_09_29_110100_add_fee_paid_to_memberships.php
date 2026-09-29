<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The fee is recorded when an application is approved, so later price changes do not rewrite past collections.
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->decimal('fee_paid', 12, 2)->default(0)->after('voucher_path');
        });

        // Existing approved members paid their type's current fee.
        DB::table('memberships')->where('status', 'approved')->update([
            'fee_paid' => DB::raw('(select coalesce(fee, 0) from membership_types where membership_types.id = memberships.membership_type_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('fee_paid');
        });
    }
};
