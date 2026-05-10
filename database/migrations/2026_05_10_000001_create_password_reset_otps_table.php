<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// This single table handles the full reset flow: OTP verification + reset token storage
// Keeping it in one table avoids sync issues between separate tables
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp_hash');              // sha256 of the 6-digit OTP
            $table->string('reset_token_hash')->nullable(); // sha256 of the reset token (set after OTP verified)
            $table->unsignedTinyInteger('attempts')->default(0); // bruteforce protection
            $table->timestamp('verified_at')->nullable(); // null until OTP is successfully verified
            $table->timestamp('expires_at');          // 10-minute window from creation
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};
