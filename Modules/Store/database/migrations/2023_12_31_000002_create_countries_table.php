<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->char('iso2', 2)->unique();
            $table->string('name', 100);
            $table->softDeletes();
        });

        // Seed default countries
        DB::table('countries')->insert([
            ['iso2' => 'US', 'name' => 'United States'],
            ['iso2' => 'CA', 'name' => 'Canada'],
            ['iso2' => 'GB', 'name' => 'United Kingdom'],
            ['iso2' => 'BD', 'name' => 'Bangladesh'],
            ['iso2' => 'IN', 'name' => 'India'],
            ['iso2' => 'PK', 'name' => 'Pakistan'],
            ['iso2' => 'AU', 'name' => 'Australia'],
            ['iso2' => 'DE', 'name' => 'Germany'],
            ['iso2' => 'FR', 'name' => 'France'],
            ['iso2' => 'JP', 'name' => 'Japan'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
