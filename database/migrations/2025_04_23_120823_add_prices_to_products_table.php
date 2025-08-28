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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('buying_price', 10, 2)->after('desc');
            $table->decimal('selling_price', 10, 2)->after('buying_price');
            $table->decimal('profit', 10, 2)->after('selling_price');
        });
    }
    
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['buying_price', 'selling_price', 'profit']);
        });
    }
    
};
