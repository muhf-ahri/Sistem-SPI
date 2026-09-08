<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('action_plans', function (Blueprint $table) {
            $table->unsignedInteger('verification_round')->default(0)->after('status');
        });

        Schema::table('follow_up_evidences', function (Blueprint $table) {
            $table->unsignedInteger('round')->default(0)->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('follow_up_evidences', function (Blueprint $table) {
            $table->dropColumn('round');
        });
        Schema::table('action_plans', function (Blueprint $table) {
            $table->dropColumn('verification_round');
        });
    }
};