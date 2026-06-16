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
        Schema::create('clients_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number');
            $table->string('collected_by');
            $table->date('due_date');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('client_loans_id')->constrained('clients_loans')->onDelete('cascade');
            $table->foreignId('client_area')->constrained('areas')->onDelete('cascade');
            $table->decimal('daily', 12, 2)->nullable();
            $table->decimal('old_balance', 12, 2)->nullable();
            $table->decimal('collection', 12, 2)->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_lapsed')->default(0);
            $table->boolean('is_collected')->default(0);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_payments');
    }
};