<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_np');
            $table->string('name_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('committee_members', function (Blueprint $table) {
            $table->foreignId('committee_type_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('show_on_homepage')->default(false)->after('is_current');
        });

        // The homepage used to list every past president; keep it looking the same.
        DB::table('committee_members')->where('is_past_president', true)->update(['show_on_homepage' => true]);
    }

    public function down(): void
    {
        Schema::table('committee_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('committee_type_id');
            $table->dropColumn('show_on_homepage');
        });

        Schema::dropIfExists('committee_types');
    }
};
