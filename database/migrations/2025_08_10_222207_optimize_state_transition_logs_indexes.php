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
        Schema::table('state_transition_logs', function (Blueprint $table) {
            // Índice compuesto para búsquedas comunes
            $table->index(['model_type', 'model_id', 'created_at'], 'state_logs_model_created_idx');

            // Índice para filtros por usuario y fecha
            $table->index(['user_id', 'created_at'], 'state_logs_user_created_idx');

            // Índice para filtros por transición
            $table->index(['state_transition_id', 'created_at'], 'state_logs_transition_created_idx');

            // Índice para logs con comentarios
            $table->index(['model_type', 'model_id'], 'state_logs_model_idx')->where('comment', '!=', null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('state_transition_logs', function (Blueprint $table) {
            $table->dropIndex('state_logs_model_created_idx');
            $table->dropIndex('state_logs_user_created_idx');
            $table->dropIndex('state_logs_transition_created_idx');
            $table->dropIndex('state_logs_model_idx');
        });
    }
};
