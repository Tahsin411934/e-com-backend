<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('register_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->decimal('expected_balance', 15, 2)->nullable();
            $table->decimal('cash_sales', 15, 2)->default(0);
            $table->decimal('card_sales', 15, 2)->default(0);
            $table->decimal('other_sales', 15, 2)->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('declared_cash', 15, 2)->nullable();
            $table->decimal('discrepancy', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed', 'reconciled'])->default('open');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['register_id', 'user_id', 'status', 'deleted_at']);
        });

        if (Schema::hasTable('pos_registers')) {
            Schema::table('pos_shifts', function (Blueprint $table) {
                $table->foreign('register_id')->references('id')->on('pos_registers')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('pos_shifts', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_shifts');
    }
};
