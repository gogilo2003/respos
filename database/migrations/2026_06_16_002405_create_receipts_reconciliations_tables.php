<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('cashier_id');
            $table->string('receipt_number', 30);
            $table->string('pdf_path', 255)->nullable();
            $table->unsignedTinyInteger('print_count')->default(0);
            $table->timestamp('generated_at')->useCurrent();

            $table->unique(['receipt_number'], 'uq_receipt_number');
            $table->foreign('bill_id')->references('id')->on('bills')->restrictOnDelete();
            $table->foreign('cashier_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('receipt_reprints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_id');
            $table->unsignedBigInteger('reprinted_by');
            $table->timestamp('reprinted_at')->useCurrent();

            $table->foreign('receipt_id')->references('id')->on('receipts')->cascadeOnDelete();
            $table->foreign('reprinted_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('cash_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->date('reconciliation_date');
            $table->unsignedBigInteger('prepared_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->decimal('system_total', 10, 2);
            $table->decimal('physical_count', 10, 2);
            $table->decimal('variance_amount', 10, 2)->virtualAs('physical_count - system_total')->stored();
            $table->decimal('variance_pct', 6, 3)->virtualAs(
                "CASE WHEN system_total = 0 THEN 0 ELSE ((physical_count - system_total) / system_total) * 100 END"
            )->stored();
            $table->boolean('flagged')->virtualAs(
                "ABS((physical_count - system_total) / system_total) > 0.005"
            )->stored();
            $table->string('notes', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['reconciliation_date'], 'uq_reconciliation_date');
            $table->foreign('prepared_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');
        Schema::dropIfExists('receipt_reprints');
        Schema::dropIfExists('receipts');
    }
};
