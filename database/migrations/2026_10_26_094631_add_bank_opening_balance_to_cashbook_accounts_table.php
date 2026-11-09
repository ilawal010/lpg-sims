<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashbook_accounts', function (Blueprint $table) {
            $table->decimal('bank_opening_balance', 19, 2)
                ->nullable()
                ->after('opening_balance');
        });
    }

    public function down(): void
    {
        Schema::table('cashbook_accounts', function (Blueprint $table) {
            $table->dropColumn('bank_opening_balance');
        });
    }
};
