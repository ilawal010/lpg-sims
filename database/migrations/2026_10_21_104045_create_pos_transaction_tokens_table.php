<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pos_transaction_tokens', function (Blueprint $table) {
            $table->id();

            $table->uuid('transaction_token')->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('sale_type', [
                'lpg',
                'accessory',
            ]);

            $table->unsignedBigInteger('sale_id')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'sale_type'],
                'pos_tokens_user_type_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_transaction_tokens');
    }
};
