<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // One authorized signature image, printed on every membership ID card.
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('authorized_signature_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('authorized_signature_url');
        });
    }
};
