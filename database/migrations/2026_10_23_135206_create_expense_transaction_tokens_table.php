<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_transaction_tokens', function (Blueprint $table) {
            $table->id();

            $table->uuid('transaction_token')->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('expense_id')
                ->nullable()
                ->constrained('expenses')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(
                ['user_id', 'expense_id'],
                'expense_tokens_user_expense_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_transaction_tokens');
    }
};