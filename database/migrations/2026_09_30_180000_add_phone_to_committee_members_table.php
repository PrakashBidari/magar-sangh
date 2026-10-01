<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('committee_members', function (Blueprint $table) {
            // Nullable only for members added before the field existed; the dashboard form requires it.
            $table->string('phone', 30)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('committee_members', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
