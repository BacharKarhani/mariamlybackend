<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->boolean('is_new')->default(false)->after('is_trending');
        $table->date('new_until')->nullable()->after('is_new');
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['is_new', 'new_until']);
    });
}

};
