<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_np');
            $table->string('name_en');
            $table->text('description')->nullable();
            // "lifetime" = never expires; otherwise the plan runs duration_value months/years from approval.
            $table->string('duration_unit', 10)->default('years');
            $table->unsignedSmallInteger('duration_value')->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_types');
    }
};
