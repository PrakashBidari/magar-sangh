<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Office staff listed on the public "Office Assistant" page under About Us.
    public function up(): void
    {
        Schema::create('office_assistants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo_url');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Adds the new "office-assistants.*" permissions.
        Permissions::sync();
    }

    public function down(): void
    {
        Schema::dropIfExists('office_assistants');
    }
};
