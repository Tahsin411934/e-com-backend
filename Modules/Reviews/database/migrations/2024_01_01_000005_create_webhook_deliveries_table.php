<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webhook_id');
            $table->string('event', 100);
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->boolean('success')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'success']);
        });

        if (Schema::hasTable('webhooks')) {
            Schema::table('webhook_deliveries', function (Blueprint $table) {
                $table->foreign('webhook_id')->references('id')->on('webhooks')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};