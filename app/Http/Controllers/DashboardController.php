<?php

namespace App\Http\Controllers;
use App\Models\Dataset;
class DashboardController extends Controller
{

    public function index()
    {
        // Total semua dataset
        $totalDataset = Dataset::count();

        // Total makanan
        $totalMakanan = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['makanan'])->count();

        // Total minuman
        $totalMinuman = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['minuman'])->count();

        return view('pages.dashboard.index', compact(
            'totalDataset',
            'totalMakanan',
            'totalMinuman'
        ));
    }

}