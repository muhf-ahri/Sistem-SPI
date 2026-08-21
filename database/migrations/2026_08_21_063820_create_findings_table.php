<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->constrained('finding_categories');
            $table->foreignId('risk_category_id')->constrained('risk_categories');
            $table->foreignId('created_by')->constrained('users');
            $table->string('finding_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->text('recommendation')->nullable();
            $table->date('deadline');
            $table->enum('status', ['open', 'in_progress', 'waiting_verification', 'closed', 'rejected'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};
