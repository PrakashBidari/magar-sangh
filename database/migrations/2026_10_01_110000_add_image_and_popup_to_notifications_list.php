<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A notification can carry an image and be shown to website visitors as a popup.
    public function up(): void
    {
        Schema::table('notifications_list', function (Blueprint $table) {
            $table->string('image_url')->nullable();
            $table->boolean('show_popup')->default(false)->index();
            $table->string('popup_pages', 10)->default('home');     // home | all
            $table->string('popup_frequency', 10)->default('once'); // once (first visit only) | always (every page load)
            $table->boolean('popup_on_exit')->default(false);       // also when the visitor is about to leave
            $table->unsignedSmallInteger('popup_repeat_minutes')->nullable(); // also again every N minutes
        });
    }

    public function down(): void
    {
        Schema::table('notifications_list', function (Blueprint $table) {
            $table->dropIndex(['show_popup']);
            $table->dropColumn(['image_url', 'show_popup', 'popup_pages', 'popup_frequency', 'popup_on_exit', 'popup_repeat_minutes']);
        });
    }
};
