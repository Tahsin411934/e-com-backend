<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('scope', ['platform', 'store'])->default('platform')->after('description');
            $table->foreignId('store_id')->nullable()->after('scope')->constrained('stores')->nullOnDelete();
            $table->index(['scope', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropIndex(['scope', 'store_id']);
            $table->dropColumn(['scope', 'store_id']);
        });
    }
};
