<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessories', function (Blueprint $table) {

            $table->id();

            /*
             * Automatically generated accessory number.
             * Example: ACC-000001
             */
            $table->string('accessory_number', 30)->unique();

            /*
             * Accessory identification.
             */
            $table->string('name', 150);

            $table->string('category', 100);

            $table->text('description')->nullable();

            /*
             * Inventory details.
             */
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->string('unit', 30)->default('piece');

            $table->decimal('quantity', 15, 3)->default(0);

            $table->decimal('reorder_level', 15, 3)->default(0);

            /*
             * Unit buying and selling prices.
             */
            $table->decimal('unit_buying_price', 18, 2)
                ->default(0);

            $table->decimal('unit_selling_price', 18, 2)
                ->default(0);

            /*
             * Operational status.
             */
            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            /*
             * Audit information.
             */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
             * Search and reporting indexes.
             */
            $table->index('name');

            $table->index('category');

            $table->index(['branch_id', 'status']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessories');
    }
};
