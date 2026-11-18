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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 16)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20)->default('online');

            $table->string('customer_name', 100);
            $table->string('customer_email', 190)->nullable();
            $table->string('customer_phone', 20)->index();
            $table->string('whatsapp_number', 20)->nullable();

            $table->string('event_type', 40);
            $table->date('event_date');
            $table->string('event_location', 255);
            $table->unsignedInteger('guest_count');
            $table->text('extra_requirements')->nullable();

            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('status_changed_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision_note', 500)->nullable();
            $table->unsignedBigInteger('quoted_amount')->nullable();

            $table->timestamps();

            $table->index(['event_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
