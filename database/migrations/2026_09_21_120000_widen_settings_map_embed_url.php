<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Google Maps embed links are often longer than 255 characters.
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('map_embed_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('map_embed_url')->nullable()->change();
        });
    }
};
