<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colocation_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 10, 2);

            $table->date('expense_date');

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('paid_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            $table->index(['colocation_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
