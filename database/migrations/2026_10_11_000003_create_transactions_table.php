<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->string('type'); // income, expense
            $table->date('transaction_date');
            $table->decimal('amount', 18, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('category')->nullable();
            $table->string('department')->nullable();
            $table->string('project')->nullable();
            $table->string('vendor')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('paid'); // unpaid, scheduled, paid, cancelled
            $table->text('description')->nullable();
            $table->string('source_reference')->nullable(); // e.g. reimbursement number
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index(['type', 'payment_status', 'transaction_date']);
            $table->index(['department', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
