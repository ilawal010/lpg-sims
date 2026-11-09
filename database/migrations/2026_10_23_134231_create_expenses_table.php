<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('expense_number', 40)->unique();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->date('expense_date');

            $table->string('category', 100);

            $table->string('description', 500);

            $table->decimal('amount', 18, 2);

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'pos',
                'other',
            ]);

            $table->string('payment_reference', 150)->nullable();

            $table->string('payee', 150)->nullable();

            $table->text('notes')->nullable();

            $table->enum('status', [
                'recorded',
                'voided',
            ])->default('recorded');

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('voided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('voided_at')->nullable();

            $table->text('void_reason')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'expense_date']);
            $table->index(['branch_id', 'status']);
            $table->index(['category', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};