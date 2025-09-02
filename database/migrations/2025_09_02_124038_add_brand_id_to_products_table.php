<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add as nullable first if you already have data, so you can backfill safely
            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete(); // avoid deleting brands with existing products
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });
    }
};
