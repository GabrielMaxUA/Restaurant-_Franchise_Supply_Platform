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
        // Check if the table already exists - it might have been created in a SQL script
        if (!Schema::hasTable('order_notifications')) {
            Schema::create('order_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->string('status');
                $table->text('message')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                
                // Add indexes for performance
                $table->index(['user_id', 'is_read']);
                $table->index('order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_notifications');
    }
};