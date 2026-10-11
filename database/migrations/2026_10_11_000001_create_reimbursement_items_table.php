<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reimbursement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reimbursement_id')->constrained()->cascadeOnDelete();
            $table->date('expense_date');
            $table->string('category');
            $table->text('description');
            $table->decimal('amount', 18, 2);
            $table->string('vendor')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->timestamps();

            $table->index(['reimbursement_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimbursement_items');
    }
};
