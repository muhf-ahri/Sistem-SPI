<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('action_plans', function (Blueprint $table) {
            $table->string('title')->after('finding_id');
            $table->foreignId('pic_user_id')->nullable()->change();
            $table->date('target_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('action_plans', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->foreignId('pic_user_id')->nullable(false)->change();
            $table->date('target_date')->nullable(false)->change();
        });
    }
};
