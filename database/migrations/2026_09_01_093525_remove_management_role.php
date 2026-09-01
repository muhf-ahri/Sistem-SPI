<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'management')->update(['role' => 'spi']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'spi', 'kepala_divisi'])->default('spi')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'spi', 'kepala_divisi', 'management'])->default('spi')->change();
        });
    }
};
