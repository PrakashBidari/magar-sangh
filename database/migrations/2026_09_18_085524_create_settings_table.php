<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name_np')->default('नेपाल मगर संघ');
            $table->string('site_name_en')->default('Nepal Magar Association');
            $table->string('tagline_np')->nullable();
            $table->string('tagline_en')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('flag_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address_np')->nullable();
            $table->string('address_en')->nullable();
            $table->string('map_embed_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('footer_credit')->nullable();
            $table->longText('history_content')->nullable();
            $table->longText('mission_vision_content')->nullable();
            $table->longText('constitution_content')->nullable();
            $table->text('about_short_np')->nullable();
            $table->text('about_short_en')->nullable();
            $table->text('president_message_np')->nullable();
            $table->string('president_name_np')->nullable();
            $table->string('president_photo_url')->nullable();
            $table->unsignedInteger('stat_members')->default(0);
            $table->unsignedInteger('stat_districts')->default(0);
            $table->unsignedInteger('stat_countries')->default(0);
            $table->unsignedInteger('stat_sister_orgs')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
