<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_notification_reads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('area_notification_id');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('area_notification_id')->references('id')->on('area_notifications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_notification_reads');
    }
};
