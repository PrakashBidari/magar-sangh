<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->index(); // income | expense
            $table->string('name', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'name']);
        });

        // Start from the categories that used to be fixed in config/accounting.php,
        // plus any other category already used in the book.
        $rows = [];
        foreach (config('accounting.categories', []) as $type => $names) {
            foreach (array_values($names) as $i => $name) {
                $rows[$type.'|'.$name] = ['type' => $type, 'name' => $name, 'sort_order' => $i + 1];
            }
        }
        foreach (DB::table('transactions')->select('type', 'category')->distinct()->get() as $used) {
            $rows[$used->type.'|'.$used->category] ??= ['type' => $used->type, 'name' => $used->category, 'sort_order' => 100];
        }

        $now = now();
        DB::table('accounting_categories')->insert(array_map(fn ($row) => $row + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now], array_values($rows)));

        // Adds the "accounting-categories.*" permissions.
        Permissions::sync();
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_categories');
    }
};
