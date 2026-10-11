<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('department');
            $table->string('category')->nullable();
            $table->string('project')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('allocated_amount', 18, 2);
            $table->decimal('used_amount', 18, 2)->default(0);
            $table->string('status')->default('active'); // draft, active, closed, cancelled
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['department', 'status', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
