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
            $table->foreignId('flatshare_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'member'])->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'flatshare_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
