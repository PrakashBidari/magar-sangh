<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // One row: the signatory and office contact details printed on every sifaris letter.
    public function up(): void
    {
        Schema::create('sifaris_settings', function (Blueprint $table) {
            $table->id();
            $table->string('signature_url')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_title')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        // Adds the new "sifaris-settings.manage" permission.
        Permissions::sync();
    }

    public function down(): void
    {
        Schema::dropIfExists('sifaris_settings');
    }
};
