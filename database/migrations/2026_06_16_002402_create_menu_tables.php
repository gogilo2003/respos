<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('table_number', 20);
            $table->unsignedTinyInteger('capacity')->default(4);
            $table->string('location', 80)->nullable();
            $table->enum('status', ['available', 'occupied', 'ordering', 'preparing', 'served', 'billing', 'paid', 'cleaning', 'reserved'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['table_number'], 'uq_table_number');
        });

        Schema::create('qr_codes', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('table_id', 36)->unique();
            $table->text('payload');
            $table->string('image_path', 255)->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('regenerated_at')->nullable();

            $table->foreign('table_id')->references('id')->on('restaurant_tables')->cascadeOnDelete();
        });

        Schema::create('menu_categories', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name', 80);
            $table->string('description', 200)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('category_id', 36);
            $table->string('name', 80);
            $table->string('description', 200)->nullable();
            $table->decimal('base_price', 10, 2);
            $table->boolean('tax_inclusive')->default(true);
            $table->unsignedTinyInteger('prep_time_min')->default(10);
            $table->string('image_url', 255)->nullable();
            $table->json('modifier_groups')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('category_id')->references('id')->on('menu_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_categories');
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('restaurant_tables');
    }
};
