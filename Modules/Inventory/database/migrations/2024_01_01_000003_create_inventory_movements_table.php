<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('inventory_locations');
            $table->unsignedBigInteger('variant_id');
            $table->enum('movement_type', ['purchase', 'sale', 'return', 'adjustment', 'transfer_in', 'transfer_out', 'reservation', 'release']);
            $table->integer('quantity');
            $table->string('reference_type', 60)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['variant_id', 'created_at'], 'inv_mov_variant_created_idx');
            $table->index(['location_id', 'created_at'], 'inv_mov_location_created_idx');
            $table->index(['reference_type', 'reference_id'], 'inv_mov_reference_idx');
        });

        if (Schema::hasTable('product_variants')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->foreign('variant_id')->references('id')->on('product_variants');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
