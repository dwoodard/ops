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
        Schema::create('signal_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->text('description');
            $table->json('category')->nullable();
            $table->string('url')->nullable();
            $table->boolean('auth_required')->default(false);
            $table->string('cost_tier')->default('free');
            $table->json('frequency_options')->nullable();
            $table->string('status')->default('active');
            $table->text('setup_instructions')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signal_sources');
    }
};
