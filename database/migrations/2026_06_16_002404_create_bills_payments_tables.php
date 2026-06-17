<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 36);
            $table->string('generated_by', 36);
            $table->enum('status', ['draft', 'open', 'partially_paid', 'paid', 'voided'])->default('draft');
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('vat_rate', 5, 2)->default(0.00);
            $table->decimal('vat_amount', 10, 2)->default(0.00);
            $table->decimal('service_charge_rate', 5, 2)->default(0.00);
            $table->decimal('service_charge_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('discount_reason', 200)->nullable();
            $table->string('discount_approved_by', 36)->nullable();
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->string('voided_by', 36)->nullable();
            $table->string('void_reason', 200)->nullable();

            $table->foreign('session_id')->references('id')->on('table_sessions')->cascadeOnDelete();
            $table->foreign('generated_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('discount_approved_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('voided_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->string('bill_id', 36);
            $table->string('order_item_id', 36);
            $table->unsignedTinyInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamp('served_at')->nullable();

            $table->foreign('bill_id')->references('id')->on('bills')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->restrictOnDelete();
        });

        Schema::create('bill_splits', function (Blueprint $table) {
            $table->id();
            $table->string('bill_id', 36);
            $table->enum('split_type', ['by_item', 'equally', 'custom']);
            $table->string('split_label', 60)->nullable();
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->enum('status', ['unpaid', 'paid', 'outstanding'])->default('unpaid');

            $table->foreign('bill_id')->references('id')->on('bills')->cascadeOnDelete();
        });

        Schema::create('bill_split_items', function (Blueprint $table) {
            $table->id();
            $table->string('split_id', 36);
            $table->string('bill_item_id', 36);

            $table->foreign('split_id')->references('id')->on('bill_splits')->cascadeOnDelete();
            $table->foreign('bill_item_id')->references('id')->on('bill_items')->restrictOnDelete();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('bill_id', 36);
            $table->string('split_id', 36)->nullable();
            $table->string('cashier_id', 36);
            $table->enum('payment_method', ['cash', 'manual'])->default('cash');
            $table->string('manual_note', 100)->nullable();
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_received', 10, 2);
            $table->decimal('change_due', 10, 2)->default(0.00);
            $table->enum('status', ['confirmed', 'refunded'])->default('confirmed');
            $table->timestamp('confirmed_at')->useCurrent();

            $table->foreign('bill_id')->references('id')->on('bills')->cascadeOnDelete();
            $table->foreign('split_id')->references('id')->on('bill_splits')->restrictOnDelete();
            $table->foreign('cashier_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id', 36);
            $table->decimal('amount', 10, 2);
            $table->string('reason', 255);
            $table->string('approved_by', 36);
            $table->string('credit_note_ref', 50);
            $table->timestamp('refunded_at')->useCurrent();

            $table->foreign('payment_id')->references('id')->on('payments')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bill_split_items');
        Schema::dropIfExists('bill_splits');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};
