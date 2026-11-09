<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('lpg_inventory_purchases', function (Blueprint $table) {
  $table->id();
  $table->foreignId('lpg_stock_receipt_id')->unique()->constrained('lpg_stock_receipts')->restrictOnDelete();
  $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
  $table->foreignId('lpg_supplier_id')->constrained('lpg_suppliers')->restrictOnDelete();
  $table->date('purchase_date');
  $table->decimal('quantity_kg',15,3);$table->decimal('unit_cost',15,4);$table->decimal('total_cost',19,4);
  $table->string('payment_status',20)->default('unpaid');
  $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
  $table->timestamps();$table->index(['branch_id','purchase_date']);
 }); }
 public function down(): void { Schema::dropIfExists('lpg_inventory_purchases'); }
};
