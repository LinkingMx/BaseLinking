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
        Schema::create('custom_styles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Frontend Styles", "Admin Overrides", etc.
            $table->text('description')->nullable();
            $table->enum('target', ['frontend', 'admin', 'both'])->default('frontend');
            $table->longText('css_content')->nullable(); // CSS content
            $table->longText('scss_content')->nullable(); // SCSS content if needed
            $table->boolean('is_active')->default(false);
            $table->integer('priority')->default(0); // Loading order
            $table->boolean('is_minified')->default(false);
            $table->longText('backup_content')->nullable(); // Last backup
            $table->string('version')->default('1.0.0');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['target', 'is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_styles');
    }
};
