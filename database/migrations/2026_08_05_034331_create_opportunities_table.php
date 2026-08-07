<?php

use App\Enums\OpportunityEngagementStatus;
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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('entity_type', ['company', 'person', 'partnership', 'event', 'website', 'community', 'other'])->default('company');
            $table->text('description')->nullable();
            $table->float('fit_score');
            $table->json('signal_ids');
            $table->string('overall_status')->default('detected');
            $table->string('engagement_status')->default(OpportunityEngagementStatus::New->value);
            $table->float('total_deal_value')->nullable();
            $table->dateTime('last_signal_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
