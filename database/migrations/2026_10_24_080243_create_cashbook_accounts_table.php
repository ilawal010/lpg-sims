<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->unique()
                ->constrained('branches')
                ->restrictOnDelete();

            $table->date('opening_date');

            $table->decimal('opening_balance', 19, 2)
                ->default(0);

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index('opening_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook_accounts');
    }
};
