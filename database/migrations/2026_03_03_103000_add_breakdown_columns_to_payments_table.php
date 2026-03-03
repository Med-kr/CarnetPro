<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('settlement_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('applied_amount', 10, 2)->nullable()->after('settlement_amount');
            $table->decimal('credit_amount', 10, 2)->nullable()->after('applied_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['settlement_amount', 'applied_amount', 'credit_amount']);
        });
    }
};
