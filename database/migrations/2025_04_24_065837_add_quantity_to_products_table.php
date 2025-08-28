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
    Schema::table('products', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->integer('quantity')->default(0)->after('selling_price');
    });
}

public function down()
{
    Schema::table('products', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->dropColumn('quantity');
    });
}

};
