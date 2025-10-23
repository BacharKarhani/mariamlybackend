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
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('buying_price', 10, 2)->nullable()->after('quantity');
            $table->decimal('regular_price', 10, 2)->nullable()->after('buying_price');
            $table->decimal('discount', 5, 2)->nullable()->after('regular_price');
            $table->decimal('selling_price', 10, 2)->nullable()->after('discount');
            $table->string('weight', 100)->nullable()->after('selling_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['buying_price', 'regular_price', 'discount', 'selling_price', 'weight']);
        });
    }
};
