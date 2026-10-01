<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Where to pay a donation: a QR image and the bank details, shown on the "Apply For Donation" page.
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('donation_qr_url')->nullable();
            $table->text('donation_bank_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['donation_qr_url', 'donation_bank_details']);
        });
    }
};
