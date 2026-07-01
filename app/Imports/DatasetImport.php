<?php

namespace App\Imports;

use App\Models\Dataset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatasetImport implements ToModel, WithHeadingRow
{
    /**
     * Convert each row to a model.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Lewati baris kosong
        if (empty($row['kode']) && empty($row['produk'])) {
            return null;
        }

        $kode = $this->getValue($row, ['kode']);
        $produk = $this->getValue($row, ['produk']);
        $kategoriBarang = $this->getValue($row, ['kategori_barang', 'kategori barang']);

        $harga = $this->toNumber($this->getValue($row, ['harga']));

        $tahunPenjualan = $this->toNumber($this->getValue($row, ['tahun_penjualan', 'tahun']));

        $tahunPenjualan = $this->toNumber($this->getValue($row, ['tahun_penjualan', 'tahun']));
        $bulan = $this->getValue($row, ['bulan', 'bulan_penjualan']);

        $totalProductFromExcel = $this->getValue($row, ['total_product', 'jumlah_product']);
        $totalPenjualanFromExcel = $this->getValue($row, ['total_penjualan']);

        $totalProduct = $totalProductFromExcel !== null && $totalProductFromExcel !== ''
            ? $this->toNumber($totalProductFromExcel)
            : 0;

        $totalPenjualan = $totalPenjualanFromExcel !== null && $totalPenjualanFromExcel !== ''
            ? $this->toNumber($totalPenjualanFromExcel)
            : ($harga * $totalProduct);

        return new Dataset([
            'kode' => $kode,
            'produk' => $produk,
            'kategori_barang' => $kategoriBarang,
            'harga' => $harga,
            'bulan' => $bulan,
            'tahun_penjualan' => $tahunPenjualan ?: date('Y'),
            'total_penjualan' => $totalPenjualan,
            'total_product' => $totalProduct,
        ]);
    }

    /**
     * Ambil nilai dari beberapa kemungkinan nama kolom.
     */
    private function getValue(array $row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return is_string($row[$key]) ? trim($row[$key]) : $row[$key];
            }
        }

        return $default;
    }

    /**
     * Ubah nilai menjadi angka integer.
     */
    private function toNumber($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        // Hapus karakter selain angka dan tanda minus
        $value = preg_replace('/[^0-9\-]/', '', (string) $value);

        return $value === '' ? 0 : (int) $value;
    }
}