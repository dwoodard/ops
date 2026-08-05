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
        Schema::table('teams', function (Blueprint $table) {
            $table->string('website')->nullable();
            $table->text('description')->nullable(); // AI-generated description from website analysis
            $table->json('enriched_data')->nullable();
            $table->timestamp('onboarded_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['website', 'description', 'enriched_data', 'onboarded_at']);
        });
    }
};
