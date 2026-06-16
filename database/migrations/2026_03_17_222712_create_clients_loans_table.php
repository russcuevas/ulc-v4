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
        Schema::create('clients_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            $table->string('pn_number')->unique();
            $table->string('release_number')->nullable();

            $table->date('loan_from');
            $table->date('loan_to');

            $table->decimal('loan_amount', 10, 2);
            $table->decimal('balance', 10, 2);

            $table->decimal('daily', 10, 2)->nullable();
            $table->decimal('principal', 10, 2);

            $table->string('loan_status')->nullable();
            $table->string('loan_terms')->nullable();

            $table->string('status')->nullable();

            $table->timestamps();
            $table->string('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_loans');
    }
};
