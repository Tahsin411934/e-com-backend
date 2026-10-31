<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->change();
            // A nullable compound index does not enforce central email uniqueness.
            $table->string('central_email')->virtualAs('CASE WHEN store_id IS NULL THEN email ELSE NULL END');
            $table->unique('central_email');
        });
    }

    public function down(): void
    {
        if (DB::table('customers')->whereNull('store_id')->exists()) {
            throw new RuntimeException('Central customers exist; migrate their accounts before rolling back.');
        }
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['central_email']);
            $table->dropColumn('central_email');
            $table->unsignedBigInteger('store_id')->nullable(false)->change();
        });
    }
};
