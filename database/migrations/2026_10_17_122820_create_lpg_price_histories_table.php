<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpg_price_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lpg_price_id')
                ->constrained('lpg_prices')
                ->restrictOnDelete();

            $table->decimal('previous_price_per_kg', 15, 2)
                ->nullable();

            $table->decimal('new_price_per_kg', 15, 2);

            $table->text('reason');

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('effective_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpg_price_histories');
    }
};
