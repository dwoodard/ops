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
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained()->cascadeOnDelete();
            $table->string('signal_type');
            $table->string('source');
            $table->string('company_name');
            $table->string('deduplication_hash')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->dateTime('detected_at');
            $table->float('relevance_score');
            $table->json('metadata')->nullable();
            $table->json('enrichment')->nullable();
            $table->json('actions_and_results')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
