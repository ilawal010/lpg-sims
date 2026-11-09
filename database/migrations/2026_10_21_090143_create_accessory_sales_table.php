<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the Accessories Sales Register.
     */
    public function up(): void
    {
        Schema::create('accessory_sales', function (Blueprint $table) {
            $table->id();

            // Permanent, unique sale identification.
            $table->string('sale_number', 50)->unique();

            // Sale date and operational branch.
            $table->date('sale_date');

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            // Accessory sold.
            $table->foreignId('accessory_id')
                ->constrained('accessories')
                ->restrictOnDelete();

            // Sales Agent responsible for the transaction.
            $table->foreignId('attendant_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Quantity and financial snapshots.
            $table->decimal('quantity', 15, 3);

            $table->decimal('unit_selling_price', 18, 2);
            $table->decimal('total_amount', 18, 2);

            $table->decimal('unit_cost_price', 18, 2);
            $table->decimal('cost_of_goods_sold', 18, 2);
            $table->decimal('gross_profit', 18, 2);

            // Link to the outgoing accessory stock movement.
            $table->foreignId('stock_movement_id')
                ->unique()
                ->constrained('accessory_stock_movements')
                ->restrictOnDelete();

            // Finalized sale information.
            $table->string('status', 20)->default('posted');
            $table->timestamp('posted_at')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            // Reporting indexes.
            $table->index(['branch_id', 'sale_date']);
            $table->index(['accessory_id', 'sale_date']);
            $table->index(['attendant_id', 'sale_date']);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessory_sales');
    }
};
