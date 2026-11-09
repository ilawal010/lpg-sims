<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->date('transaction_date');

            $table->string('transaction_number', 60)->unique();

            $table->enum('transaction_type', [
                'receipt',
                'payment',
            ]);

            $table->string('category', 100);

            $table->string('description', 500);

            $table->decimal('amount', 19, 2);

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'pos',
                'other',
            ]);

            $table->string('payment_reference', 150)->nullable();

            // Originating record, where applicable.
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            // Manual transactions have no source record.
            $table->boolean('is_manual')->default(false);

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

            // Prevent importing one source transaction twice.
            $table->unique(
                ['source_type', 'source_id'],
                'cashbook_unique_source'
            );

            // Efficient branch-based reporting.
            $table->index(
                ['branch_id', 'transaction_date'],
                'cashbook_branch_date'
            );

            $table->index(
                ['branch_id', 'status'],
                'cashbook_branch_status'
            );

            $table->index(
                ['transaction_type', 'transaction_date'],
                'cashbook_type_date'
            );

            $table->index(
                ['payment_method', 'transaction_date'],
                'cashbook_method_date'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook_transactions');
    }
};
