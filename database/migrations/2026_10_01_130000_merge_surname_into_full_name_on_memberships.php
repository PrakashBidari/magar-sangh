<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The application form now asks for the full name only: existing surnames are appended to it.
    public function up(): void
    {
        DB::table('memberships')->whereNotNull('surname')->where('surname', '!=', '')->orderBy('id')
            ->each(fn ($m) => DB::table('memberships')->where('id', $m->id)->update(['full_name' => trim($m->full_name.' '.$m->surname)]));

        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('surname');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->string('surname')->nullable();
        });
    }
};
