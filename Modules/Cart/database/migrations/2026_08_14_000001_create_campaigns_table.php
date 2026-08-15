<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('button_text', 60)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'active', 'paused'])->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'starts_at', 'ends_at']);
        });

        Schema::create('campaign_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'fixed_price']);
            $table->decimal('discount_value', 19, 4);
            $table->timestamps();
            $table->unique(['campaign_id', 'product_id', 'variant_id'], 'campaign_product_variant_unique');
            $table->index(['product_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_products');
        Schema::dropIfExists('campaigns');
    }
};
