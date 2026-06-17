<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->ulid('public_id')->unique()->index();
            $table->string('key', 80);
            $table->string('value', 255);
            $table->string('description', 200)->nullable();
            $table->string('updated_by', 36)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['key']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['customer', 'waiter', 'kitchen', 'cashier', 'manager', 'admin'])->unique();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('restrict');
            $table->string('name', 100);
            $table->string('username', 60)->unique();
            $table->string('email', 100)->nullable();
            $table->string('password_hash', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('system_settings');
    }
};
