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
        Schema::create('custom_forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('set null');
            $table->foreignId('landing_page_id')->nullable()->constrained('landing_pages')->onDelete('set null');
            $table->json('fields')->nullable();
            $table->timestamps();
        });

        Schema::create('form_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('custom_forms')->onDelete('cascade');
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_results');
        Schema::dropIfExists('custom_forms');
    }
};
