@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Edit Dataset</h4>
            </div>

            <div class="card-body">
                <form id="editDatasetForm" action="{{ route('dataset.update', $dataset->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Kode</label>
                            <input type="text" name="kode" class="form-control"
                                value="{{ old('kode', $dataset->kode) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Nama Produk</label>
                            <input type="text" name="produk" class="form-control"
                                value="{{ old('produk', $dataset->produk) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Kategori Barang</label>
                            <select name="kategori_barang" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <option value="makanan"
                                    {{ old('kategori_barang', $dataset->kategori_barang) == 'makanan' ? 'selected' : '' }}>
                                    Makanan
                                </option>
                                <option value="minuman"
                                    {{ old('kategori_barang', $dataset->kategori_barang) == 'minuman' ? 'selected' : '' }}>
                                    Minuman
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Harga</label>
                            <input type="number" name="harga" class="form-control"
                                value="{{ old('harga', $dataset->harga) }}" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Januari</label>
                            <input type="number" name="january_jumlah_product" class="form-control"
                                value="{{ old('january_jumlah_product', $dataset->january_jumlah_product) }}" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Februari</label>
                            <input type="number" name="februari_jumlah_product" class="form-control"
                                value="{{ old('februari_jumlah_product', $dataset->februari_jumlah_product) }}" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Maret</label>
                            <input type="number" name="maret_jumlah_product" class="form-control"
                                value="{{ old('maret_jumlah_product', $dataset->maret_jumlah_product) }}" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>April</label>
                            <input type="number" name="april_jumlah_product" class="form-control"
                                value="{{ old('april_jumlah_product', $dataset->april_jumlah_product) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Total Penjualan</label>
                            <input type="number" name="total_penjualan" class="form-control"
                                value="{{ old('total_penjualan', $dataset->total_penjualan) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Total Produk</label>
                            <input type="number" name="total_product" class="form-control"
                                value="{{ old('total_product', $dataset->total_product) }}" required>
                        </div>

                    </div>

                    <div class="mt-3">
                        <!-- Trigger Modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmModal">
                            Update
                        </button>

                        <a href="{{ route('dataset.index') }}" class="btn btn-secondary">
                            Kembali
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Update</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    Yakin ingin mengupdate data ini?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" form="editDatasetForm">
                        Ya, Update
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection
