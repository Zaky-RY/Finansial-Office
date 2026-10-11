<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('budget_id')
                ->nullable()
                ->after('reimbursement_id')
                ->constrained('budgets')
                ->nullOnDelete();

            $table->index(['budget_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropIndex(['budget_id', 'payment_status']);
            $table->dropColumn('budget_id');
        });
    }
};
