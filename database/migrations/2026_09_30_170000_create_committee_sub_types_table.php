<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A committee can be split into sub types, e.g. District Committee → Kathmandu, Dhading...
        Schema::create('committee_sub_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_type_id')->constrained()->cascadeOnDelete();
            $table->string('name_np');
            $table->string('name_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('committee_members', function (Blueprint $table) {
            $table->foreignId('committee_sub_type_id')->nullable()->after('committee_type_id')->constrained()->nullOnDelete();
        });

        // Adds the "committee-sub-types.*" permissions.
        Permissions::sync();
    }

    public function down(): void
    {
        Schema::table('committee_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('committee_sub_type_id');
        });

        Schema::dropIfExists('committee_sub_types');
    }
};
