<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Titled PDF files listed (with a download button) on the Lakhan Thapa Pratisthan page.
    public function up(): void
    {
        Schema::create('donation_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_documents');
    }
};
