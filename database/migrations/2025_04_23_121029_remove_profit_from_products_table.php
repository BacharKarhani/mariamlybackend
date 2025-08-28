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
            $table->dropColumn('profit');
        });
    }
    
    public function down()
    {
        Schema::table('products', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->decimal('profit', 10, 2)->nullable();
        });
    }
    
};
