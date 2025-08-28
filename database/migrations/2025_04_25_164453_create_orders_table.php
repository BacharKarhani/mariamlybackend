<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('payment_code'); // Ex: cash, stripe, etc.
            $table->string('logistic')->nullable(); // shipping company name
            $table->string('track')->nullable();    // tracking number
            $table->string('order_status')->default('pending'); // pending, processing, shipped, etc.
            $table->timestamp('date_added')->useCurrent();
            $table->timestamp('date_modified')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
