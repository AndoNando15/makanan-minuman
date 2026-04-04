@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        </div>
        <div class="row">
            {{-- Kartu total dataset --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Dataset
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $totalDataset ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-database fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Kartu Makanan --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Makanan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $totalMakanan ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-utensils fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Minuman --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Minuman
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $totalMinuman ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-coffee fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kalau nanti mau tambah card lain, bisa di sini --}}
            {{-- 
            <div class="col-xl-3 col-md-6 mb-4">
                ...
            </div>
            --}}
        </div>

        <!-- Penjelasan metode K-Means -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Penjelasan Sistem
                        </h6>
                    </div>
                    <div class="card-body">
                        <p>
                            Aplikasi ini digunakan untuk mengelola dan menganalisis data
                            <strong>produk makanan dan minuman</strong> berdasarkan data penjualan.
                            Data yang digunakan meliputi <strong>harga, total produk terjual, dan total penjualan</strong>.
                        </p>

                        <p>
                            Sebelum dilakukan proses analisis, data akan melalui tahap
                            <strong>normalisasi Min-Max</strong> agar setiap variabel memiliki skala yang sama (0–1).
                            Hal ini penting agar perhitungan tidak bias terhadap nilai yang lebih besar.
                        </p>

                        <p>
                            Setelah normalisasi, data dapat dianalisis menggunakan
                            <strong>metode K-Means Clustering</strong> untuk mengelompokkan produk ke dalam beberapa
                            cluster.
                        </p>

                        <p class="mb-2"><strong>Tujuan penggunaan K-Means:</strong></p>
                        <ul>
                            <li>Mengelompokkan produk berdasarkan performa penjualan</li>
                            <li>Mengetahui produk dengan kategori <strong>tinggi, sedang, atau rendah</strong></li>
                            <li>Membantu pengambilan keputusan dalam strategi penjualan</li>
                        </ul>

                        <p class="mb-2"><strong>Alur proses sistem:</strong></p>
                        <ol>
                            <li>Input atau import dataset produk</li>
                            <li>Melakukan normalisasi data (Min-Max)</li>
                            <li>Menentukan jumlah cluster (K)</li>
                            <li>Menghitung jarak antar data menggunakan Euclidean Distance</li>
                            <li>Mengelompokkan data ke cluster terdekat</li>
                            <li>Melakukan iterasi hingga hasil cluster stabil</li>
                        </ol>

                        <p class="mb-0">
                            Hasil clustering akan membantu dalam memahami pola penjualan produk,
                            sehingga dapat digunakan sebagai dasar dalam pengambilan keputusan bisnis.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
