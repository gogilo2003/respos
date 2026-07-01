<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->enum('request_type', ['assistance', 'bill_request'])->default('assistance');
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->enum('status', ['pending', 'handled'])->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('handled_at')->nullable();

            $table->foreign('session_id')->references('id')->on('table_sessions')->cascadeOnDelete();
            $table->foreign('handled_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('target_role', ['customer', 'waiter', 'kitchen', 'cashier', 'admin']);
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->string('event_type', 60);
            $table->json('payload')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_role', 'is_read'], 'idx_notif_role_read');
            $table->foreign('session_id')->references('id')->on('table_sessions')->cascadeOnDelete();
            $table->foreign('target_user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('entity_type', 60);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id'], 'idx_audit_entity');
            $table->index(['user_id'], 'idx_audit_user');
            $table->index(['created_at'], 'idx_audit_created');
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('assistance_requests');
    }
};
