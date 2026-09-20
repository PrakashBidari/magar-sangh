<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sister_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name_np');
            $table->string('name_en')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('blurb')->nullable();
            $table->string('link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sister_organizations');
    }
};
