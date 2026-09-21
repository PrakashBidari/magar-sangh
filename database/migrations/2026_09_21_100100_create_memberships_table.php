<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_type_id')->constrained()->restrictOnDelete();

            // Assigned on approval and never reused (derived from this row's auto-increment id).
            $table->string('membership_number', 20)->nullable()->unique();
            $table->string('status', 10)->default('pending')->index(); // pending | approved | rejected

            $table->string('full_name');
            $table->string('surname');
            $table->date('date_of_birth');
            $table->string('permanent_address');
            $table->string('current_address');
            $table->string('province');
            $table->string('district');
            $table->string('municipality');
            $table->string('ward_no', 10);
            $table->string('mobile', 20);
            $table->string('email');
            $table->string('occupation');

            $table->string('photo_url');
            $table->string('signature_url')->nullable();
            $table->string('voucher_path')->nullable(); // private disk

            $table->date('applied_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
