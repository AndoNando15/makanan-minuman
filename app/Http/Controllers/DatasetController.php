<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;
use App\Imports\DatasetImport;
use Maatwebsite\Excel\Facades\Excel;

class DatasetController extends Controller
{
    public function index()
    {
        $makanan = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['makanan'])
            ->orderByRaw('LEFT(kode, 1) ASC')
            ->orderByRaw('CAST(SUBSTRING(kode, 2) AS UNSIGNED) ASC')
            ->get();

        $minuman = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['minuman'])
            ->orderByRaw('LEFT(kode, 1) ASC')
            ->orderByRaw('CAST(SUBSTRING(kode, 2) AS UNSIGNED) ASC')
            ->get();

        $normalisasiMakanan = $this->normalisasiMinMax($makanan);
        $normalisasiMinuman = $this->normalisasiMinMax($minuman);

        return view('pages.dataset.index', compact(
            'makanan',
            'minuman',
            'normalisasiMakanan',
            'normalisasiMinuman'
        ));
    }

    public function create()
    {
        return view('pages.dataset.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:255',
            'produk' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'january_jumlah_product' => 'required|integer',
            'februari_jumlah_product' => 'required|integer',
            'maret_jumlah_product' => 'required|integer',
            'april_jumlah_product' => 'required|integer',
            'total_penjualan' => 'required|numeric',
            'total_product' => 'required|integer',
        ]);

        Dataset::create([
            'kode' => $request->kode,
            'produk' => $request->produk,
            'kategori_barang' => $request->kategori_barang,
            'harga' => $request->harga,
            'january_jumlah_product' => $request->january_jumlah_product,
            'februari_jumlah_product' => $request->februari_jumlah_product,
            'maret_jumlah_product' => $request->maret_jumlah_product,
            'april_jumlah_product' => $request->april_jumlah_product,
            'total_penjualan' => $request->total_penjualan,
            'total_product' => $request->total_product,
        ]);

        return redirect()->route('dataset.index')->with('success', 'Dataset created successfully!');
    }

    public function edit($id)
    {
        $dataset = Dataset::findOrFail($id);
        return view('pages.dataset.edit', compact('dataset'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => 'required|string|max:255',
            'produk' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'january_jumlah_product' => 'required|integer',
            'februari_jumlah_product' => 'required|integer',
            'maret_jumlah_product' => 'required|integer',
            'april_jumlah_product' => 'required|integer',
            'total_penjualan' => 'required|numeric',
            'total_product' => 'required|integer',
        ]);

        $dataset = Dataset::findOrFail($id);
        $dataset->update([
            'kode' => $request->kode,
            'produk' => $request->produk,
            'kategori_barang' => $request->kategori_barang,
            'harga' => $request->harga,
            'january_jumlah_product' => $request->january_jumlah_product,
            'februari_jumlah_product' => $request->februari_jumlah_product,
            'maret_jumlah_product' => $request->maret_jumlah_product,
            'april_jumlah_product' => $request->april_jumlah_product,
            'total_penjualan' => $request->total_penjualan,
            'total_product' => $request->total_product,
        ]);

        return redirect()->route('dataset.index')->with('success', 'Dataset updated successfully!');
    }

    public function destroy($id)
    {
        $dataset = Dataset::findOrFail($id);
        $dataset->delete();

        return redirect()->route('dataset.index')->with('success', 'Dataset deleted successfully!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Dataset::truncate();
        Excel::import(new DatasetImport, $request->file('file'));

        return redirect()->route('dataset.index')->with('success', 'Dataset imported successfully!');
    }

    private function normalisasiMinMax($data)
    {
        if ($data->isEmpty()) {
            return collect([]);
        }

        $minHarga = $data->min('harga');
        $maxHarga = $data->max('harga');

        $minTotalProduct = $data->min('total_product');
        $maxTotalProduct = $data->max('total_product');

        $minTotalPenjualan = $data->min('total_penjualan');
        $maxTotalPenjualan = $data->max('total_penjualan');

        return $data->map(function ($item) use ($minHarga, $maxHarga, $minTotalProduct, $maxTotalProduct, $minTotalPenjualan, $maxTotalPenjualan) {
            return [
                'kode' => $item->kode,
                'harga' => $this->hitungMinMax($item->harga, $minHarga, $maxHarga),
                'total_product' => $this->hitungMinMax($item->total_product, $minTotalProduct, $maxTotalProduct),
                'total_penjualan' => $this->hitungMinMax($item->total_penjualan, $minTotalPenjualan, $maxTotalPenjualan),
            ];
        });
    }
    public function deleteAll()
    {
        Dataset::truncate(); // hapus semua data
        return redirect()->route('dataset.index')->with('success', 'Semua data berhasil dihapus!');


    }
    private function hitungMinMax($value, $min, $max)
    {
        if ($max == $min) {
            return 0;
        }

        return round(($value - $min) / ($max - $min), 3);
    }
}