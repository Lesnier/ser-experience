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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendee_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('cascade');
            
            // Códigos QR Únicos y Separados
            $table->string('entry_code')->unique(); // Para acceso en puerta
            $table->string('loyalty_code')->unique(); // Para consumo de cupones
            
            $table->string('status')->default('pending'); // pending, confirmed, checked_in, cancelled
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checkout_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
