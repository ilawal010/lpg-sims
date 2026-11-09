<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_transaction_tokens', function (Blueprint $table) {
            $table->dropColumn('request_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('expense_transaction_tokens', function (Blueprint $table) {
            $table->char('request_fingerprint', 64)
                ->nullable()
                ->after('user_id');
        });
    }
};