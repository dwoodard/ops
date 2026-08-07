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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('recommendation_type');
            $table->json('content');
            $table->float('confidence_score');
            $table->json('score_breakdown')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('executed_at')->nullable();
            $table->foreignId('executed_by')->nullable(); // Assuming this references a user or system that executed the recommendation
            $table->text('result_comment')->nullable(); // Optional comment about the result of the recommendation
            $table->boolean('auto_generated')->default(false); // Flag to indicate if the recommendation was auto-generated or manually created, example: by a user or system
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
