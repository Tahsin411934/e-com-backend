<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campaigns') && ! Schema::hasColumn('campaigns', 'is_central_campaign')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->boolean('is_central_campaign')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campaigns') && Schema::hasColumn('campaigns', 'is_central_campaign')) {
            Schema::table('campaigns', fn (Blueprint $table) => $table->dropColumn('is_central_campaign'));
        }
    }
};
