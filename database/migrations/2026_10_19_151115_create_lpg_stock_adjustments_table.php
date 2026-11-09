<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpg_stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->string('adjustment_reference', 60)->unique();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('lpg_storage_unit_id')
                ->constrained('lpg_storage_units')
                ->restrictOnDelete();

            $table->date('adjustment_date');

            $table->enum('adjustment_type', [
                'adjustment_in',
                'adjustment_out',
            ]);

            $table->decimal('quantity_kg', 15, 3);

            $table->string('reason', 500);

            $table->text('notes')->nullable();

            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'rejected',
            ])->default('draft');

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->foreignId('stock_movement_id')
                ->nullable()
                ->unique()
                ->constrained('lpg_stock_movements')
                ->restrictOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->index(
                ['branch_id', 'adjustment_date'],
                'lpg_adjustments_branch_date_idx'
            );

            $table->index(
                ['lpg_storage_unit_id', 'status'],
                'lpg_adjustments_unit_status_idx'
            );

            $table->index(
                ['status', 'adjustment_date'],
                'lpg_adjustments_status_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpg_stock_adjustments');
    }
};
