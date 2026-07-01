@extends('layouts.base')

@section('content')
    <div class="container-fluid">

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Dataset Makanan</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <a href="{{ route('dataset.create') }}" class="btn btn-primary">Tambah Dataset</a>

                        <button class="btn btn-info" data-toggle="modal" data-target="#importModal">
                            Import Dataset
                        </button>

                        <!-- DELETE ALL BUTTON -->
                        <button class="btn btn-danger" data-toggle="modal" data-target="#deleteAllModal">
                            Delete All
                        </button>
                    </div>

                    <div>
                        <button class="btn btn-success" data-toggle="modal" data-target="#normalisasiModal">
    Normalisasi
</button>
<a href="{{ asset('template/dataset_template.xlsx') }}" class="btn btn-secondary ml-2">Download Template</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTableMakanan" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Produk</th>
                                <th>Kategori Barang</th>
                                <th>Harga</th>
                                <th>Tahun</th>
                                <th>Bulan</th>
                                <th>Jumlah Product</th>
                                <th>Total Penjualan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($makanan as $dataset)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dataset->kode }}</td>
                                    <td>{{ $dataset->produk }}</td>
                                    <td>{{ $dataset->kategori_barang }}</td>
                                    <td>{{ $dataset->harga }}</td>
                                    <td>{{ $dataset->tahun_penjualan }}</td>
                                    <td>{{ $dataset->bulan }}</td>
                                    <td>{{ $dataset->total_product }}</td>
                                    <td>{{ $dataset->total_penjualan }}</td>
                                    <td>
                                        <a href="{{ route('dataset.edit', $dataset->id) }}"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('dataset.destroy', $dataset->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">Belum ada data makanan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Dataset Minuman</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTableMinuman" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Produk</th>
                                <th>Kategori Barang</th>
                                <th>Harga</th>
                                <th>Tahun</th>
                                <th>Bulan</th>
                                <th>Jumlah Product</th>
                                <th>Total Penjualan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($minuman as $dataset)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dataset->kode }}</td>
                                    <td>{{ $dataset->produk }}</td>
                                    <td>{{ $dataset->kategori_barang }}</td>
                                    <td>{{ $dataset->harga }}</td>
                                    <td>{{ $dataset->tahun_penjualan }}</td>
                                    <td>{{ $dataset->bulan }}</td>
                                    <td>{{ $dataset->total_product }}</td>
                                    <td>{{ $dataset->total_penjualan }}</td>
                                    <td>
                                        <a href="{{ route('dataset.edit', $dataset->id) }}"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('dataset.destroy', $dataset->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">Belum ada data minuman</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal for Importing Dataset -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Dataset</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Import Form -->
                    <form action="{{ route('dataset.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="file">Upload Excel File</label>
                            <input type="file" class="form-control" name="file" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Normalisasi -->
    <div class="modal fade" id="normalisasiModal" tabindex="-1" role="dialog" aria-labelledby="normalisasiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="normalisasiModalLabel">Hasil Normalisasi Min-Max</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5 class="font-weight-bold text-center mb-3">
                        NORMALISASI MIN - MAX DATA TRANSAKSI PENJUALAN PRODUK JENIS MAKANAN
                    </h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Harga</th>
                                    <th>Total Product</th>
                                    <th>Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($normalisasiMakanan as $item)
                                    <tr>
                                        <td>{{ $item['kode'] }}</td>
                                        <td>{{ number_format($item['harga'], 3) }}</td>
                                        <td>{{ number_format($item['total_product'], 3) }}</td>
                                        <td>{{ number_format($item['total_penjualan'], 3) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada data makanan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h5 class="font-weight-bold text-center mb-3">
                        NORMALISASI MIN - MAX DATA TRANSAKSI PENJUALAN PRODUK JENIS MINUMAN
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Harga</th>
                                    <th>Total Product</th>
                                    <th>Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($normalisasiMinuman as $item)
                                    <tr>
                                        <td>{{ $item['kode'] }}</td>
                                        <td>{{ number_format($item['harga'], 3) }}</td>
                                        <td>{{ number_format($item['total_product'], 3) }}</td>
                                        <td>{{ number_format($item['total_penjualan'], 3) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada data minuman</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Delete All -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">Konfirmasi Hapus Semua Data</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <p class="mb-2">
                        ⚠️ Semua data <strong>makanan & minuman</strong> akan dihapus!
                    </p>
                    <p class="text-danger font-weight-bold">
                        Aksi ini tidak dapat dikembalikan.
                    </p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">
                        Batal
                    </button>

                    <form action="{{ route('dataset.deleteAll') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            Ya, Hapus Semua
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('css')
    <!-- Custom styles for this page -->
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@push('script')
    <!-- Page level plugins -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function() {
            if ($.fn.dataTable.isDataTable('#dataTableMakanan')) {
                $('#dataTableMakanan').DataTable().destroy();
            }

            if ($.fn.dataTable.isDataTable('#dataTableMinuman')) {
                $('#dataTableMinuman').DataTable().destroy();
            }

            $('#dataTableMakanan').DataTable({
                "pageLength": 5,
                "lengthMenu": [5, 10, 25, 50, 100],
                "order": []
            });

            $('#dataTableMinuman').DataTable({
                "pageLength": 5,
                "lengthMenu": [5, 10, 25, 50, 100],
                "order": []
            });
        });
    </script>
@endpush
