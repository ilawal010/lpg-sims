<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpg_stock_receipts', function (Blueprint $table) {
            $table->id();

            // Receipt identification
            $table->string('receipt_number', 50)->unique();
            $table->date('receipt_date');

            // Supplier and receiving location
            $table->foreignId('lpg_supplier_id')
                ->constrained('lpg_suppliers')
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('lpg_storage_unit_id')
                ->constrained('lpg_storage_units')
                ->restrictOnDelete();

            // Supplier delivery documentation
            $table->string('supplier_invoice_number', 100)->nullable();
            $table->string('delivery_note_number', 100)->nullable();
            $table->string('vehicle_registration_number', 50)->nullable();
            $table->string('driver_name', 150)->nullable();

            // LPG quantity and purchase valuation
            $table->decimal('quantity_kg', 15, 3);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total_cost', 19, 4);

            // Receipt lifecycle
            $table->string('status', 20)->default('draft');
            $table->timestamp('posted_at')->nullable();

            // Link to the stock ledger after posting
            $table->foreignId('stock_movement_id')
                ->nullable()
                ->unique()
                ->constrained('lpg_stock_movements')
                ->restrictOnDelete();

            // Notes and accountability
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('posted_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            // Reporting and lookup indexes
            $table->index(['status', 'receipt_date']);
            $table->index(['lpg_supplier_id', 'receipt_date']);
            $table->index(['branch_id', 'lpg_storage_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpg_stock_receipts');
    }
};
