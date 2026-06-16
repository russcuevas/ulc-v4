<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients_loans', function (Blueprint $table) {
            $table->boolean('loan_start_sms_sent')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_loans', function (Blueprint $table) {
            $table->dropColumn('loan_start_sms_sent');
        });
    }
};
