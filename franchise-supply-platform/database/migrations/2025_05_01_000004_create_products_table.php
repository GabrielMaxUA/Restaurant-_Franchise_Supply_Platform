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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0.00);
            $table->unsignedInteger('category_id')->nullable();
            $table->integer('inventory_count')->default(0);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->index(['category_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};