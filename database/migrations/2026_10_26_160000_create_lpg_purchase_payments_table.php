<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lpg_purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpg_inventory_purchase_id')->constrained('lpg_inventory_purchases')->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 19, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'pos', 'other']);
            $table->string('payment_reference', 150)->nullable();
            $table->string('transaction_token', 36)->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['lpg_inventory_purchase_id', 'payment_date'], 'lpg_purchase_payment_date_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('lpg_purchase_payments'); }
};
