<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('homepage_ctas') && !Schema::hasColumn('homepage_ctas', 'is_central_cta')) Schema::table('homepage_ctas', fn (Blueprint $t) => $t->boolean('is_central_cta')->default(false)->index());
        if (Schema::hasTable('announcement_bars') && !Schema::hasColumn('announcement_bars', 'is_central_announcement')) Schema::table('announcement_bars', fn (Blueprint $t) => $t->boolean('is_central_announcement')->default(false)->index());
    }
    public function down(): void {
        if (Schema::hasColumn('homepage_ctas', 'is_central_cta')) Schema::table('homepage_ctas', fn (Blueprint $t) => $t->dropColumn('is_central_cta'));
        if (Schema::hasColumn('announcement_bars', 'is_central_announcement')) Schema::table('announcement_bars', fn (Blueprint $t) => $t->dropColumn('is_central_announcement'));
    }
};
