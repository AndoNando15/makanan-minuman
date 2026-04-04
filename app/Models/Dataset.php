<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    use HasFactory;

    protected $table = 'dataset';

    protected $fillable = [
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
    ];

    protected $casts = [
        'harga' => 'integer',
        'january_jumlah_product' => 'integer',
        'februari_jumlah_product' => 'integer',
        'maret_jumlah_product' => 'integer',
        'april_jumlah_product' => 'integer',
        'total_penjualan' => 'integer',
        'total_product' => 'integer',
    ];
}