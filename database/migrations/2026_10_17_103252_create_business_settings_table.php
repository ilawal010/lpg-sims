<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();

            // Company profile
            $table->string('business_name')
                ->default('Intisar Oil & Gas');

            $table->string('trading_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();

            // Receipt information
            $table->string('receipt_heading')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->string('receipt_prefix', 20)
                ->default('INT');

            // LPG operations
            $table->string('lpg_unit', 10)->default('kg');
            $table->decimal('low_stock_threshold_kg', 12, 3)
                ->default(100);

            // Financial settings
            $table->string('currency_code', 3)->default('NGN');
            $table->string('currency_symbol', 10)->default('₦');
            $table->string('costing_method', 30)
                ->default('weighted_average');

            // System preferences
            $table->string('timezone', 60)
                ->default('Africa/Lagos');

            $table->string('date_format', 30)
                ->default('d/m/Y');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
