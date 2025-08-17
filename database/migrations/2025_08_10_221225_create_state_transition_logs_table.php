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
        Schema::create('state_transition_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // App\Models\Documentation
            $table->unsignedBigInteger('model_id'); // ID del documento
            $table->foreignId('state_transition_id')->constrained('state_transitions');
            $table->foreignId('from_state_id')->constrained('approval_states');
            $table->foreignId('to_state_id')->constrained('approval_states');
            $table->foreignId('user_id')->constrained('users');
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable(); // IP, user agent, etc.
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_transition_logs');
    }
};
