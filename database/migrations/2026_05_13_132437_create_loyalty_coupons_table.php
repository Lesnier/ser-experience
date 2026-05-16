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
        Schema::create('loyalty_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('discount_type')->default('percentage'); // percentage, fixed_amount, freebie
            $table->decimal('discount_value', 10, 2)->default(0.00);
            
            $table->integer('global_limit')->nullable(); // Límite global de la campaña
            $table->integer('usage_limit_per_attendee')->default(1); // 1, 2 o 3 usos por persona
            
            $table->string('allocation_strategy')->default('general'); // general (todos), selective (solo asignados)
            $table->string('validity_scope')->default('during_event'); // during_event, post_event, both
            
            $table->boolean('allow_brand_modification')->default(false); // El organizador controla este permiso
            $table->boolean('is_active')->default(true);
            
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_coupons');
    }
};
