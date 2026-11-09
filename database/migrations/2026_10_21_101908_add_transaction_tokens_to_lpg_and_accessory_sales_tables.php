<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpg_sales', function (Blueprint $table) {
            $table->uuid('transaction_token')
                ->nullable()
                ->unique();
        });

        Schema::table('accessory_sales', function (Blueprint $table) {
            $table->uuid('transaction_token')
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('accessory_sales', function (Blueprint $table) {
            $table->dropUnique(
                ['transaction_token']
            );

            $table->dropColumn('transaction_token');
        });

        Schema::table('lpg_sales', function (Blueprint $table) {
            $table->dropUnique(
                ['transaction_token']
            );

            $table->dropColumn('transaction_token');
        });
    }
};
