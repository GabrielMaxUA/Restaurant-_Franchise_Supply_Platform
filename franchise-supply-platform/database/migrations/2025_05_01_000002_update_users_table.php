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
        Schema::table('users', function (Blueprint $table) {
            // Drop default Laravel columns and add custom ones
            $table->dropColumn(['name', 'email_verified_at', 'remember_token']);
            
            // Add custom columns to match the SQL schema
            $table->string('username', 50)->after('id');
            $table->string('password_hash', 255)->after('username');
            $table->string('email', 100)->change();
            $table->string('fcm_token', 255)->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('fcm_token');
            $table->unsignedInteger('role_id')->after('phone');
            $table->string('updated_by', 100)->nullable()->after('updated_at');
            $table->boolean('status')->default(1)->comment('1 = active, 0 = blocked')->after('updated_by');
            $table->boolean('email_notifications_enabled')->default(1)->after('status');
            
            // Add foreign key constraint
            $table->foreign('role_id')->references('id')->on('roles');
        });

        // Rename password column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'username', 'password_hash', 'fcm_token', 'phone', 
                'role_id', 'updated_by', 'status', 'email_notifications_enabled'
            ]);
            
            $table->string('name')->after('id');
            $table->string('email')->change();
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password')->after('email_verified_at');
            $table->rememberToken()->after('password');
        });
    }
};