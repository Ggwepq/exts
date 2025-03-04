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
        Schema::create('installment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_id')->constrained('recurring_transactions')->onDelete('cascade');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_transactions');
    }
};
