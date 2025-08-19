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
        Schema::create('manual_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'usuarios', 'workflows', etc.
            $table->string('name'); // 'Gestión de Usuarios', etc.
            $table->string('description')->nullable();
            $table->string('icon')->nullable(); // heroicon name
            $table->string('color')->default('primary'); // filament color
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_categories');
    }
};
