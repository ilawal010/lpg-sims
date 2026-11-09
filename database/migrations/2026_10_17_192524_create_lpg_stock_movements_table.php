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
        Schema::create('lpg_stock_movements', function (Blueprint $table) {
            $table->id();

            // Physical inventory location
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('lpg_storage_unit_id')
                ->constrained('lpg_storage_units')
                ->restrictOnDelete();

            // Unique reference for each ledger entry
            $table->string('movement_reference', 60)->unique();

            // Classification of inventory movement
            $table->enum('movement_type', [
                'opening_stock',
                'stock_receipt',
                'sale',
                'transfer_in',
                'transfer_out',
                'adjustment_in',
                'adjustment_out',
            ]);

            // Direction: in = addition, out = deduction
            $table->enum('direction', ['in', 'out']);

            // Quantity always recorded as a positive value
            $table->decimal('quantity_kg', 15, 3);

            // Cost information for inventory valuation
            $table->decimal('unit_cost', 18, 4)->nullable();
            $table->decimal('total_cost', 20, 4)->nullable();

            // Transaction information
            $table->dateTime('movement_date');
            $table->string('document_reference', 100)->nullable();
            $table->string('description', 500)->nullable();

            // For linking related records such as transfers
            $table->string('transaction_group', 80)->nullable();

            // Responsible staff
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Reporting and balance calculation indexes
            $table->index(
                ['lpg_storage_unit_id', 'movement_date', 'id'],
                'lpg_movements_unit_date_idx'
            );

            $table->index(
                ['branch_id', 'movement_date'],
                'lpg_movements_branch_date_idx'
            );

            $table->index(
                ['movement_type', 'movement_date'],
                'lpg_movements_type_date_idx'
            );

            $table->index('transaction_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpg_stock_movements');
    }
};
