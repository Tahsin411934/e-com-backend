<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('tax_rates')) {
            Schema::create('tax_rates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 220);
                $table->decimal('rate', 8, 4)->default(0.0000);
                $table->enum('type', ['percentage', 'fixed'])->default('percentage');
                $table->enum('applies_to', ['all', 'products', 'services', 'digital'])->default('all');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->boolean('is_default')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tax_rates');
    }
};
