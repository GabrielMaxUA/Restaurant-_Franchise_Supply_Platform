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
        Schema::create('franchisee_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('company_name', 100);
            $table->string('address', 255);
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('contact_name', 100)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->timestamps();
            $table->string('updated_by', 100)->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('franchisee_details');
    }
};