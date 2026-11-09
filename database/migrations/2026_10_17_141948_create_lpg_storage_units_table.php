<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpg_storage_units', function (Blueprint $table) {
            $table->id();

            /*
             * Every storage unit belongs to a branch.
             * Restrict deletion to preserve inventory history.
             */
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            /*
             * System-generated unique storage identifier.
             */
            $table->string('storage_code', 40)->unique();

            /*
             * Storage type:
             * tank = bulk LPG storage tank
             * cylinder = LPG storage/dispensing cylinder
             */
            $table->enum('storage_type', [
                'tank',
                'cylinder',
            ]);

            $table->string('storage_name', 150);

            /*
             * Rated LPG storage capacity in kilograms.
             * This is not the current LPG stock balance.
             */
            $table->decimal('capacity_kg', 15, 3);

            /*
             * Optional manufacturer information.
             */
            $table->string('manufacturer', 150)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('installation_date')->nullable();

            /*
             * Operational status.
             */
            $table->enum('status', [
                'active',
                'inactive',
                'maintenance',
                'decommissioned',
            ])->default('active');

            $table->text('notes')->nullable();

            /*
             * Audit ownership.
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
             * Indexes for branch-level inventory reporting.
             */
            $table->index(
                ['branch_id', 'storage_type'],
                'lpg_storage_branch_type_idx'
            );

            $table->index(
                ['branch_id', 'status'],
                'lpg_storage_branch_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpg_storage_units');
    }
};
