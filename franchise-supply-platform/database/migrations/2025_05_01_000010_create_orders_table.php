<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->enum('status', ['pending', 'approved', 'rejected', 'packed', 'shipped', 'delivered'])->default('pending');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('shipping_address', 255);
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_zip', 20)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_time', 20)->nullable();
            $table->string('delivery_preference', 20)->default('standard');
            $table->decimal('shipping_cost', 8, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->string('manager_name', 100)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('purchase_order', 50)->nullable();
            $table->timestamps();
            $table->timestamp('approved_at')->nullable();
            $table->string('qb_invoice_id', 100)->nullable();
            $table->string('invoice_number', 50)->nullable();
            $table->string('email_access_token', 100)->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('email_access_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};