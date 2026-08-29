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
        Schema::create('product_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name', 160);
            $table->string('customer_email', 160);
            $table->string('customer_phone', 30)->nullable();
            $table->string('product_name', 220);
            $table->text('product_description')->nullable();
            $table->string('product_image', 255)->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('expected_price', 12, 4)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('customer_email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
