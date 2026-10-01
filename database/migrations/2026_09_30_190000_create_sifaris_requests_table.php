<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sifaris_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 10)->default('pending')->index(); // pending | approved | rejected

            // Printed on the letter (see resources/views/dashboard/sifaris/_letter.blade.php).
            $table->string('applicant_name');
            $table->string('parent_relation', 20);      // छोरा | छोरी | श्रीमती
            $table->string('parent_name');
            $table->string('grandparent_relation', 20); // नाति | नातिनी | बुहारी
            $table->string('grandparent_name');
            $table->string('province');
            $table->string('district');
            $table->string('municipality');
            $table->string('ward_no', 10);
            $table->string('photo_url');

            // Contact details for the office only.
            $table->string('mobile', 20);
            $table->string('email')->nullable();

            // Filled in by the office when approving.
            $table->string('letter_number', 50)->nullable();
            $table->string('dispatch_number', 50)->nullable();
            $table->string('letter_date', 50)->nullable(); // free text, usually a Bikram Sambat date

            $table->date('applied_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Adds the new "sifaris.*" permissions.
        Permissions::sync();
    }

    public function down(): void
    {
        Schema::dropIfExists('sifaris_requests');
    }
};
