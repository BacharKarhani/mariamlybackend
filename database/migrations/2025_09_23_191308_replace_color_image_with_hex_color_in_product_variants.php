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
            // Remove color_image column
            $table->dropColumn('color_image');
            
            // Add hex_color column
            $table->string('hex_color', 7)->nullable()->after('color');
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
            // Remove hex_color column
            $table->dropColumn('hex_color');
            
            // Add back color_image column
            $table->string('color_image')->nullable()->after('color');
        });
    }
};
