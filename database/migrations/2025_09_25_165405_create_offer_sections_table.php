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
        Schema::create('offer_sections', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->nullable();
            $table->string('alt_text')->default('Offer Image');
            $table->string('discount_percentage')->nullable(); // e.g., "60% Off"
            $table->string('title')->nullable(); // e.g., "Celebrate love & beauty this Valentine's!"
            $table->text('description')->nullable(); // e.g., "Get our exclusive cosmetic Valentine gifts..."
            $table->string('button_text')->default('Customize now');
            $table->string('button_link')->default('/shop');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_sections');
    }
};
