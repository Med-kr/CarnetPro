<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colocation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('role', ['owner', 'member'])->default('member');
            $table->integer('reputation_score')->default(0);

            $table->timestamp('left_at')->nullable();

            $table->timestamps();

            // ما نخليوش نفس user يدخل نفس colocation جوج مرات
            $table->unique(['colocation_id', 'user_id']);

            $table->index(['user_id', 'left_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
