@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Tambah Dataset</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('dataset.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Kode</label>
                            <input type="text" name="kode" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Nama Produk</label>
                            <input type="text" name="produk" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Kategori Barang</label>
                            <select name="kategori_barang" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <option value="makanan">Makanan</option>
                                <option value="minuman">Minuman</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Harga</label>
                            <input type="number" name="harga" class="form-control" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Tahun Penjualan</label>
                            <select name="tahun_penjualan" class="form-control" required>
                                <option value="">-- Pilih Tahun --</option>
                                @php
                                    $currentYear = date('Y');
                                @endphp
                                @for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++)
                                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Bulan Penjualan</label>
                            <select name="bulan" class="form-control" required>
                                <option value="">-- Pilih Bulan --</option>
                                <option value="Januari">Januari</option>
                                <option value="Februari">Februari</option>
                                <option value="Maret">Maret</option>
                                <option value="April">April</option>
                                <option value="Mei">Mei</option>
                                <option value="Juni">Juni</option>
                                <option value="Juli">Juli</option>
                                <option value="Agustus">Agustus</option>
                                <option value="September">September</option>
                                <option value="Oktober">Oktober</option>
                                <option value="November">November</option>
                                <option value="Desember">Desember</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Total Penjualan</label>
                            <input type="number" name="total_penjualan" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Jumlah Produk</label>
                            <input type="number" name="total_product" class="form-control" required>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                        <a href="{{ route('dataset.index') }}" class="btn btn-secondary">
                            Kembali
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
