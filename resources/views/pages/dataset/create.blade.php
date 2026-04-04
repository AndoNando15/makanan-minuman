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
                            <label>Januari</label>
                            <input type="number" name="january_jumlah_product" class="form-control" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Februari</label>
                            <input type="number" name="februari_jumlah_product" class="form-control" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Maret</label>
                            <input type="number" name="maret_jumlah_product" class="form-control" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>April</label>
                            <input type="number" name="april_jumlah_product" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Total Penjualan</label>
                            <input type="number" name="total_penjualan" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Total Produk</label>
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
