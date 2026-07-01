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
        Schema::table('dataset', function (Blueprint $table) {
            $table->dropColumn([
                'january_jumlah_product',
                'februari_jumlah_product',
                'maret_jumlah_product',
                'april_jumlah_product',
            ]);
            $table->string('bulan')->nullable()->after('harga');
            $table->integer('tahun_penjualan')->default(date('Y'))->after('bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dataset', function (Blueprint $table) {
            $table->dropColumn(['bulan', 'tahun_penjualan']);
            $table->integer('january_jumlah_product')->default(0);
            $table->integer('februari_jumlah_product')->default(0);
            $table->integer('maret_jumlah_product')->default(0);
            $table->integer('april_jumlah_product')->default(0);
        });
    }
};
