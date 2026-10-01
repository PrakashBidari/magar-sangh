<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending')->index()->after('donate_date');
            $table->text('review_note')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('review_note')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        // Donations entered before this change were added by admins and are already on the site.
        DB::table('donations')->update(['status' => 'approved', 'reviewed_at' => now()]);

        // Adds the new "donations.approve" permission.
        Permissions::sync();
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'review_note', 'reviewed_at']);
        });
    }
};
