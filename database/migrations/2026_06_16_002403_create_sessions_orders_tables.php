<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_sessions', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('table_id', 36);
            $table->string('session_token', 64)->unique();
            $table->string('opened_by', 36)->nullable();
            $table->enum('open_source', ['customer_qr', 'waiter', 'cashier'])->default('customer_qr');
            $table->enum('status', ['open', 'billing', 'closed', 'force_closed'])->default('open');
            $table->unsignedTinyInteger('customer_count')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamp('token_expires_at');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->string('closed_by', 36)->nullable();
            $table->string('close_reason', 200)->nullable();

            $table->foreign('table_id')->references('id')->on('restaurant_tables')->cascadeOnDelete();
            $table->foreign('opened_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('closed_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('session_id', 36);
            $table->enum('placed_by_role', ['customer', 'waiter', 'cashier']);
            $table->string('placed_by_user', 36)->nullable();
            $table->enum('status', ['pending', 'accepted', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->string('cancel_reason', 200)->nullable();
            $table->string('cancelled_by', 36)->nullable();
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('first_ready_at')->nullable();
            $table->timestamp('fully_served_at')->nullable();
            $table->string('notes', 255)->nullable();

            $table->foreign('session_id')->references('id')->on('table_sessions')->cascadeOnDelete();
            $table->foreign('placed_by_user')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('cancelled_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('order_id', 36);
            $table->string('menu_item_id', 36);
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->string('special_instructions', 120)->nullable();
            $table->json('modifiers')->nullable();
            $table->enum('status', ['pending', 'accepted', 'preparing', 'ready', 'served', 'cancelled', 'voided'])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('void_reason', 200)->nullable();
            $table->string('voided_by', 36)->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->cascadeOnDelete();
            $table->foreign('voided_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('table_sessions');
    }
};
