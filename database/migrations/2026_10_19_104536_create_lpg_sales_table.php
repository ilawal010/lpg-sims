<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpg_sales', function (Blueprint $table) {
            $table->id();

            $table->string('sale_number', 60)->unique();
            $table->date('sale_date');

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('lpg_storage_unit_id')
                ->constrained('lpg_storage_units')
                ->restrictOnDelete();

            $table->foreignId('attendant_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('quantity_kg', 15, 3);

            $table->decimal('selling_price_per_kg', 19, 2);
            $table->decimal('total_amount', 19, 2);

            $table->decimal('cost_price_per_kg', 19, 4)
                ->nullable();

            $table->decimal('cost_of_goods_sold', 19, 4)
                ->nullable();

            $table->decimal('gross_profit', 19, 4)
                ->nullable();

            $table->string('payment_method', 30)
                ->nullable();

            $table->string('payment_reference', 100)
                ->nullable();

            $table->string('status', 20)
                ->default('draft');

            $table->foreignId('stock_movement_id')
                ->nullable()
                ->unique()
                ->constrained('lpg_stock_movements')
                ->restrictOnDelete();

            $table->timestamp('posted_at')->nullable();

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

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'sale_date']);
            $table->index(['lpg_storage_unit_id', 'status']);
            $table->index(['attendant_id', 'sale_date']);
            $table->index(['status', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpg_sales');
    }
};
