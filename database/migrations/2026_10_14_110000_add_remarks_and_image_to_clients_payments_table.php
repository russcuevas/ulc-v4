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
        Schema::table('clients_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('clients_payments', 'remarks')) {
                $table->text('remarks')->nullable()->after('type');
            }
            if (!Schema::hasColumn('clients_payments', 'image')) {
                $table->string('image')->nullable()->after('remarks');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_payments', function (Blueprint $table) {
            if (Schema::hasColumn('clients_payments', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('clients_payments', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
