<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A ledger of money received. The booking's amount paid and balance are
     * always calculated from confirmed rows here, never stored. Rows are never
     * deleted; mistakes are voided with a reason.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->string('receipt_number', 20)->unique();
            $table->unsignedBigInteger('amount');
            $table->string('method', 20);
            $table->string('reference', 100)->nullable();
            $table->dateTime('received_at');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('note', 255)->nullable();
            $table->string('status', 20)->default('confirmed');
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
