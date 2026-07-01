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
        'bulan',
        'tahun_penjualan',
        'total_penjualan',
        'total_product',
    ];

    protected $casts = [
        'harga' => 'integer',
        'tahun_penjualan' => 'integer',
        'total_penjualan' => 'integer',
        'total_product' => 'integer',
    ];
}