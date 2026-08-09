<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('bill_number')->unique()->after('id');
        });

        $prefix = 'BILL';
        $year = now()->year;

        DB::table('bills')->orderBy('id')->get()->each(function ($bill) use ($prefix, $year) {
            $sequence = str_pad((string) $bill->id, 6, '0', STR_PAD_LEFT);
            DB::table('bills')->where('id', $bill->id)->update([
                'bill_number' => "{$prefix}-{$year}-{$sequence}",
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn('bill_number');
        });
    }
};
