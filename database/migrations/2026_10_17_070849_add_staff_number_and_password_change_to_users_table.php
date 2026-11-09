<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('staff_number', 30)
                ->nullable()
                ->unique();

            $table->boolean('must_change_password')
                ->default(true);
        });

        /*
         * Assign a staff number to the existing
         * Owner / Super Admin account.
         *
         * Preserve the account, password and role.
         */
        DB::table('users')
            ->where('email', 'ilawal010@gmail.com')
            ->whereNull('staff_number')
            ->update([
                'staff_number' => 'INT-0001',
                'must_change_password' => false,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropUnique('users_staff_number_unique');

            $table->dropColumn([
                'staff_number',
                'must_change_password',
            ]);
        });
    }
};
