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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->text('permissions')->nullable();
        });

        // Insert default roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin', 'permissions' => 'all'],
            ['id' => 2, 'name' => 'warehouse', 'permissions' => 'view_orders,update_orders,view_products'],
            ['id' => 3, 'name' => 'franchisee', 'permissions' => 'place_orders,view_products'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};