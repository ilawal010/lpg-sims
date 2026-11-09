<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessory_stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accessory_id')
                ->constrained('accessories')
                ->restrictOnDelete();

            $table->string('movement_type', 30);

            $table->decimal('previous_quantity', 15, 3);
            $table->decimal('quantity_added', 15, 3);
            $table->decimal('new_quantity', 15, 3);

            $table->decimal('unit_buying_price', 18, 2)
                ->nullable();

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'accessory_id',
                'movement_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessory_stock_movements');
    }
};