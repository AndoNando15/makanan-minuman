<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop old columns
        Schema::table('dataset', function (Blueprint $table) {
            $table->dropColumn([
                'nama_platform_e_wallet',
                'VTP',
                'NTP',
                'PPE',
                'FPE',
                'PSD',
                'IPE',
                'PKP',
            ]);
        });

        // Add new columns
        Schema::table('dataset', function (Blueprint $table) {
            $table->string('kode')->after('id');
            $table->string('produk');
            $table->string('kategori_barang');
            $table->bigInteger('harga')->default(0);
            $table->integer('january_jumlah_product')->default(0);
            $table->integer('februari_jumlah_product')->default(0);
            $table->integer('maret_jumlah_product')->default(0);
            $table->integer('april_jumlah_product')->default(0);
            $table->bigInteger('total_penjualan')->default(0);
            $table->integer('total_product')->default(0);
        });
    }

    public function down(): void
    {
        // Remove new columns
        Schema::table('dataset', function (Blueprint $table) {
            $table->dropColumn([
                'kode',
                'produk',
                'kategori_barang',
                'harga',
                'january_jumlah_product',
                'februari_jumlah_product',
                'maret_jumlah_product',
                'april_jumlah_product',
                'total_penjualan',
                'total_product',
            ]);
        });

        // Restore old columns
        Schema::table('dataset', function (Blueprint $table) {
            $table->string('nama_platform_e_wallet')->nullable();
            $table->integer('VTP')->nullable();
            $table->integer('NTP')->nullable();
            $table->integer('PPE')->nullable();
            $table->integer('FPE')->nullable();
            $table->integer('PSD')->nullable();
            $table->integer('IPE')->nullable();
            $table->integer('PKP')->nullable();
        });
    }
};