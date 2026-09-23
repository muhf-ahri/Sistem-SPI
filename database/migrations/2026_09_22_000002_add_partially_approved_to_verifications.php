<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah nilai enum 'partially_approved' pada kolom result verifikasi
        DB::statement("ALTER TABLE verifications MODIFY result ENUM('approved', 'rejected', 'partially_approved') NOT NULL DEFAULT 'approved'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE verifications MODIFY result ENUM('approved', 'rejected') NOT NULL DEFAULT 'approved'");
    }
};