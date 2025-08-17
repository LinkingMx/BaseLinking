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
        Schema::table('approval_states', function (Blueprint $table) {
            $table->string('state_class')->nullable()->after('name');
            $table->index('state_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_states', function (Blueprint $table) {
            $table->dropIndex(['state_class']);
            $table->dropColumn('state_class');
        });
    }
};
