<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_stock_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->string('item_type', 20);

            $table->unsignedBigInteger('item_id');

            $table->decimal('minimum_stock', 15, 3)
                ->nullable();

            $table->decimal('target_stock', 15, 3);

            $table->boolean('is_active')
                ->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'item_type', 'item_id'],
                'procurement_settings_item_unique'
            );

            $table->index(
                ['branch_id', 'item_type', 'is_active'],
                'procurement_settings_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_stock_settings');
    }
};