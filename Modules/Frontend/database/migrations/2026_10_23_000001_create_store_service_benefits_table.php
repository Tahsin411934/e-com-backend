<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Frontend\Database\Seeders\ServiceBenefitPermissionsSeeder;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_service_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('description', 255)->nullable();
            $table->string('icon', 40);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['store_id', 'is_active', 'sort_order']);
        });

        (new ServiceBenefitPermissionsSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('store_service_benefits');
    }
};
