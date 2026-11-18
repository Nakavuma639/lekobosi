<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Requests turned away because the date was already full, kept so the
     * owner can see lost demand and call the customer back.
     */
    public function up(): void
    {
        Schema::create('booking_conflict_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20);
            $table->string('customer_email', 190)->nullable();
            $table->string('event_type', 40);
            $table->date('requested_date')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_conflict_attempts');
    }
};
