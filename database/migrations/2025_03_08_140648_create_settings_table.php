<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('currency_code');
            $table->foreignId('theme_id')->nullable()->constrained('themes')->onDelete('set null');
            $table->foreignId('notification_type_id')->nullable()->constrained('notification_types')->onDelete('set null');
            $table->foreignId('backup_settings_id')->nullable()->constrained('backup_settings')->onDelete('set null');
            $table->string('language', 10)->default('en');
            $table->timestamps();

            $table->foreign('currency_code')->references('code')->on('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
