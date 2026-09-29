<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Rich text (with images and PDFs) shown above the donation list on the Lakhan Thapa Pratisthan page.
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->longText('donation_page_content')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('donation_page_content');
        });
    }
};
