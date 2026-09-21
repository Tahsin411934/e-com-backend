<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->decimal('limit_value', 12, 2)->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'feature_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('plan_features'); }
};
