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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('secretary_id');
            $table->foreign('secretary_id')->references('id')->on('secretaries')->onDelete('cascade');
            $table->unsignedBigInteger('collector_id');
            $table->foreign('collector_id')->references('id')->on('collectors')->onDelete('cascade');
            $table->string('location_name');
            $table->string('areas_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
