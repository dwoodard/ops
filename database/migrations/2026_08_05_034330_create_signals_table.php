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
            $table->string('signal_type'); // decided by AI
            $table->string('source'); // source of the signal (e.g., news, social media, etc.)
            $table->string('company_name'); // company name associated with the signal
            $table->text('description')->nullable();
            $table->string('url')->nullable(); // URL to the source of the signal
            $table->dateTime('detected_at'); // when the signal was detected
            $table->float('relevance_score'); // score indicating the relevance of the signal to the objective
            $table->json('metadata')->nullable(); // additional metadata related to the signal
            $table->json('actions_and_results')->nullable(); // JSON field to store actions taken and their results
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
