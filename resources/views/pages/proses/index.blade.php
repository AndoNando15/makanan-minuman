@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary">Proses Kmeans</h4>
                @if (!empty($hasilMakanan['iterationsUsed']) || !empty($hasilMinuman['iterationsUsed']))
                    <div>
                        @if (!empty($hasilMakanan['iterationsUsed']))
                            <span class="badge badge-info mr-1">
                                Makanan: {{ $hasilMakanan['iterationsUsed'] }} iterasi
                            </span>
                        @endif
                        @if (!empty($hasilMinuman['iterationsUsed']))
                            <span class="badge badge-info">
                                Minuman: {{ $hasilMinuman['iterationsUsed'] }} iterasi
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="card-body">

                {{-- Kartu total dataset --}}
                <div class="col-xl-3 col-md-6 mb-4 p-0">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Dataset
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDataset ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-database fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $selectedMakananIds = !empty($hasilMakanan['selectedDatasets'])
                        ? collect($hasilMakanan['selectedDatasets'])->pluck('id')->toArray()
                        : [];

                    $selectedMinumanIds = !empty($hasilMinuman['selectedDatasets'])
                        ? collect($hasilMinuman['selectedDatasets'])->pluck('id')->toArray()
                        : [];
                @endphp

                {{-- Form pilih cluster dan centroid awal --}}
                <form action="{{ route('proses.cluster') }}" method="POST" id="kmeansForm">
                    @csrf

                    <div class="form-group">
                        <label for="cluster">Select Cluster</label>
                        <select name="cluster" id="cluster" class="form-control" required>
                            <option value="" disabled {{ empty($selectedCluster) ? 'selected' : '' }}>
                                Select Cluster
                            </option>
                            @foreach ([2, 3, 4, 5] as $k)
                                <option value="{{ $k }}"
                                    {{ isset($selectedCluster) && $selectedCluster == $k ? 'selected' : '' }}>
                                    Cluster {{ $k }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @foreach ([2, 3, 4, 5] as $n)
                        <div id="block-{{ $n }}" class="cluster-block" style="display:none">
                            <h5 class="mt-3">Centroid Awal Makanan</h5>
                            @for ($i = 0; $i < $n; $i++)
                                <label class="mt-2">Select Dataset Makanan {{ $i + 1 }}</label>
                                <select name="dataset_makanan_id[]" class="form-control cluster-{{ $n }}"
                                    disabled required>
                                    <option value="">Select Dataset Makanan {{ $i + 1 }}</option>
                                    @foreach ($allDatasetsMakanan as $d)
                                        <option value="{{ $d->id }}"
                                            {{ isset($selectedMakananIds[$i]) && $selectedMakananIds[$i] == $d->id ? 'selected' : '' }}>
                                            {{ $d->kode }} - {{ $d->produk }}
                                        </option>
                                    @endforeach
                                </select>
                            @endfor

                            <h5 class="mt-4">Centroid Awal Minuman</h5>
                            @for ($i = 0; $i < $n; $i++)
                                <label class="mt-2">Select Dataset Minuman {{ $i + 1 }}</label>
                                <select name="dataset_minuman_id[]" class="form-control cluster-{{ $n }}"
                                    disabled required>
                                    <option value="">Select Dataset Minuman {{ $i + 1 }}</option>
                                    @foreach ($allDatasetsMinuman as $d)
                                        <option value="{{ $d->id }}"
                                            {{ isset($selectedMinumanIds[$i]) && $selectedMinumanIds[$i] == $d->id ? 'selected' : '' }}>
                                            {{ $d->kode }} - {{ $d->produk }}
                                        </option>
                                    @endforeach
                                </select>
                            @endfor
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary mt-3">Proses</button>
                </form>

                @php
                    $sections = [
                        [
                            'title' => 'Kategori Makanan',
                            'key' => 'makanan',
                            'hasil' => $hasilMakanan,
                            'canvasId' => 'clusterScatterChartMakanan',
                            'tabId' => 'tab-makanan',
                            'tabBtnId' => 'tab-makanan-btn',
                        ],
                        [
                            'title' => 'Kategori Minuman',
                            'key' => 'minuman',
                            'hasil' => $hasilMinuman,
                            'canvasId' => 'clusterScatterChartMinuman',
                            'tabId' => 'tab-minuman',
                            'tabBtnId' => 'tab-minuman-btn',
                        ],
                    ];

                    $hasResults =
                        !empty($hasilMakanan['selectedDatasets']) || !empty($hasilMinuman['selectedDatasets']);
                @endphp

                @if ($hasResults)
                    <div class="mt-5">
                        <ul class="nav nav-tabs" id="hasilTab" role="tablist">
                            @foreach ($sections as $index => $section)
                                @php
                                    $isActive = $index === 0 ? 'active' : '';
                                    $isSelected = $index === 0 ? 'true' : 'false';
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $isActive }}" id="{{ $section['tabBtnId'] }}"
                                        data-toggle="tab" href="#{{ $section['tabId'] }}" role="tab"
                                        aria-controls="{{ $section['tabId'] }}" aria-selected="{{ $isSelected }}">
                                        {{ $section['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="hasilTabContent">
                            @foreach ($sections as $index => $section)
                                @php
                                    $hasil = $section['hasil'];
                                    $isActive = $index === 0 ? 'show active' : '';
                                @endphp

                                <div class="tab-pane fade {{ $isActive }}" id="{{ $section['tabId'] }}"
                                    role="tabpanel" aria-labelledby="{{ $section['tabBtnId'] }}">

                                    @if (!empty($hasil) && !empty($hasil['selectedDatasets']))
                                        <div class="card shadow-sm mt-4">
                                            <div class="card-header bg-light">
                                                <h4 class="mb-0 text-primary">{{ $section['title'] }}</h4>
                                            </div>

                                            <div class="card-body">

                                                {{-- Tabel hasil pilihan --}}
                                                <div class="mt-2">
                                                    <h5 class="mb-3">Hasil Pilihan Dataset (Centroid Awal)</h5>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">No</th>
                                                                <th class="text-center">ID</th>
                                                                <th class="text-center">Kode</th>
                                                                <th class="text-start">Produk</th>
                                                                <th class="text-center">Kategori</th>
                                                                <th class="text-center">Harga</th>
                                                                <th class="text-center">Total Product</th>
                                                                <th class="text-center">Total Penjualan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($hasil['normalizedSelectedDatasets'] as $row)
                                                                <tr>
                                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                                    <td class="text-center">{{ $row['id'] }}</td>
                                                                    <td class="text-center">{{ $row['kode'] }}</td>
                                                                    <td class="text-start">{{ $row['produk'] }}</td>
                                                                    <td class="text-center">{{ $row['kategori_barang'] }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                        {{ number_format($row['harga'], 3) }}</td>
                                                                    <td class="text-center">
                                                                        {{ number_format($row['total_product'], 3) }}</td>
                                                                    <td class="text-center">
                                                                        {{ number_format($row['total_penjualan'], 3) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                {{-- Grouped Tables per Iteration --}}
                                                @if (
                                                    !empty($hasil['allIterations']) ||
                                                        !empty($hasil['allDistancesPerIteration']) ||
                                                        !empty($hasil['allClusterResultsPerIteration']))
                                                    @foreach ($hasil['allIterations'] as $iterationIndex => $iteration)
                                                        <div class="mt-4">
                                                            <div class="mt-5">
                                                                <div class="text-center"
                                                                    style="background-color: #ecf7ff; border-radius: 8px;">
                                                                    <h5 class="text-primary font-weight-bold"
                                                                        style="font-size: 1.5rem;">
                                                                        Iterasi {{ $iterationIndex + 1 }}
                                                                    </h5>
                                                                </div>
                                                            </div>

                                                            <h6 class="font-weight-bold mt-3">Perhitungan Jarak Euclidean
                                                            </h6>

                                                            @if (isset($hasil['allDistancesPerIteration'][$iterationIndex]))
                                                                <table class="table table-bordered table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">No</th>
                                                                            <th class="text-center">Kode</th>
                                                                            <th class="text-start">Produk</th>

                                                                            @for ($i = 1; $i <= $selectedCluster; $i++)
                                                                                <th class="text-center">Jarak ke
                                                                                    C{{ $i }}</th>
                                                                            @endfor

                                                                            <th class="text-center">Jarak Terdekat</th>
                                                                            <th class="text-center">Cluster Terdekat</th>
                                                                            <th class="text-center">Perubahan</th>
                                                                            {{-- <th class="text-center">Jarak Terdekat ^2</th> --}}
                                                                        </tr>
                                                                    </thead>

                                                                    <tbody>
                                                                        @foreach ($hasil['allDistancesPerIteration'][$iterationIndex] as $i => $row)
                                                                            <tr>
                                                                                <td class="text-center">
                                                                                    {{ $i + 1 }}</td>
                                                                                <td class="text-center">
                                                                                    {{ $row['dataset']->kode ?? '-' }}</td>
                                                                                <td class="text-start">
                                                                                    {{ $row['dataset']->produk ?? '-' }}
                                                                                </td>

                                                                                @foreach ($row['distances'] as $d)
                                                                                    <td class="text-center">
                                                                                        {{ number_format($d, 3) }}</td>
                                                                                @endforeach

                                                                                <td class="text-center">
                                                                                    {{ number_format($row['dmin'], 3) }}
                                                                                </td>

                                                                                <td class="text-center">
                                                                                    @php
                                                                                        $color = match (
                                                                                            $row['nearest']
                                                                                        ) {
                                                                                            1 => 'primary',
                                                                                            2 => 'warning',
                                                                                            3 => 'success',
                                                                                            4 => 'danger',
                                                                                            5 => 'info',
                                                                                            default => 'secondary',
                                                                                        };
                                                                                    @endphp
                                                                                    <span
                                                                                        class="badge badge-{{ $color }}">C{{ $row['nearest'] }}</span>
                                                                                </td>

                                                                                <td class="text-center">
                                                                                    @if (($row['changed'] ?? '') === 'Iya')
                                                                                        <span
                                                                                            class="badge badge-danger">Iya</span>
                                                                                    @else
                                                                                        <span
                                                                                            class="badge badge-success">Tidak</span>
                                                                                    @endif
                                                                                </td>

                                                                                {{-- <td class="text-center">
                                                                                    {{ number_format($row['dminSquared'], 4) }}
                                                                                </td> --}}
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            @endif

                                                            {{-- Tabel Hasil Cluster per Iterasi --}}
                                                            <h6 class="font-weight-bold mt-3">Menetapkan data ke kelas
                                                                terdekat</h6>
                                                            @if (isset($hasil['allClusterResultsPerIteration'][$iterationIndex]))
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">Cluster</th>
                                                                            <th>Produk</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($hasil['allClusterResultsPerIteration'][$iterationIndex] as $result)
                                                                            <tr>
                                                                                <td class="text-center">
                                                                                    @php
                                                                                        $clusterValue =
                                                                                            $result['cluster'] ?? 0;
                                                                                        $color = 'primary';

                                                                                        switch ($clusterValue) {
                                                                                            case 1:
                                                                                                $color = 'primary';
                                                                                                break;
                                                                                            case 2:
                                                                                                $color = 'warning';
                                                                                                break;
                                                                                            case 3:
                                                                                                $color = 'success';
                                                                                                break;
                                                                                            case 4:
                                                                                                $color = 'danger';
                                                                                                break;
                                                                                            case 5:
                                                                                                $color = 'info';
                                                                                                break;
                                                                                            default:
                                                                                                $color = 'secondary';
                                                                                                break;
                                                                                        }
                                                                                    @endphp

                                                                                    <span
                                                                                        class="badge badge-{{ $color }}">
                                                                                        C{{ $clusterValue }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>{{ $result['products'] ?? '-' }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            @endif

                                                            {{-- Tabel Hasil Iterasi --}}
                                                            <h6 class="font-weight-bold mt-4">Centroid baru</h6>
                                                            <table class="table table-bordered text-center">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Cluster</th>
                                                                        @foreach ($hasil['features'] as $feature)
                                                                            <th>{{ $feature }}</th>
                                                                        @endforeach
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($iteration['centroids'] as $index2 => $centroid)
                                                                        <tr>
                                                                            <td>
                                                                                @php
                                                                                    $clusterNum = $index2 + 1;
                                                                                    $color = 'primary';

                                                                                    switch ($clusterNum) {
                                                                                        case 1:
                                                                                            $color = 'primary';
                                                                                            break;
                                                                                        case 2:
                                                                                            $color = 'warning';
                                                                                            break;
                                                                                        case 3:
                                                                                            $color = 'success';
                                                                                            break;
                                                                                        case 4:
                                                                                            $color = 'danger';
                                                                                            break;
                                                                                        case 5:
                                                                                            $color = 'info';
                                                                                            break;
                                                                                        default:
                                                                                            $color = 'secondary';
                                                                                            break;
                                                                                    }
                                                                                @endphp

                                                                                <span
                                                                                    class="badge badge-{{ $color }}">C{{ $clusterNum }}</span>
                                                                            </td>
                                                                            @foreach ($hasil['features'] as $f)
                                                                                <td>{{ number_format($centroid[$f] ?? 0, 4) }}
                                                                                </td>
                                                                            @endforeach
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>

                                                            {{-- SSE Per Iterasi --}}
                                                            @if (isset($hasil['allSSEPerIteration'][$iterationIndex]))
                                                                <div class="mt-3">
                                                                    <div
                                                                        class="d-flex justify-content-start align-items-center p-2 border rounded bg-light">
                                                                        <div>
                                                                            <div class="font-weight-semibold mr-2">
                                                                                SSE Iterasi {{ $iterationIndex + 1 }} :
                                                                            </div>
                                                                        </div>

                                                                        <span class="badge badge-primary">
                                                                            {{ number_format($hasil['allSSEPerIteration'][$iterationIndex], 4) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @endif

                                                {{-- Hasil Konvergen --}}
                                                @if (!empty($hasil['newCentroids']))
                                                    <div class="card shadow-sm mt-4">
                                                        <div class="card-header bg-primary text-white">
                                                            <h4 class="mb-0 text-center">Hasil Konvergen</h4>
                                                        </div>

                                                        <div class="m-4">
                                                            {{-- Centroid akhir --}}
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="card shadow-sm">
                                                                        <div class="card-header">
                                                                            <h5 class="mb-0">Centroid Akhir (Konvergen)
                                                                            </h5>
                                                                        </div>

                                                                        <div class="card-body">
                                                                            <div class="table-responsive">
                                                                                <table
                                                                                    class="table table-sm table-bordered text-center align-middle mb-0">
                                                                                    <thead class="table-light">
                                                                                        <tr>
                                                                                            <th style="width: 10%">Cluster
                                                                                            </th>
                                                                                            @foreach ($hasil['features'] as $feature)
                                                                                                <th>{{ $feature }}
                                                                                                </th>
                                                                                            @endforeach
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        @foreach ($hasil['newCentroids'] as $index3 => $centroid)
                                                                                            <tr>
                                                                                                <td>
                                                                                                    @php
                                                                                                        $clusterNum =
                                                                                                            $index3 + 1;
                                                                                                        $color =
                                                                                                            'primary';

                                                                                                        switch (
                                                                                                            $clusterNum
                                                                                                        ) {
                                                                                                            case 1:
                                                                                                                $color =
                                                                                                                    'primary';
                                                                                                                break;
                                                                                                            case 2:
                                                                                                                $color =
                                                                                                                    'warning';
                                                                                                                break;
                                                                                                            case 3:
                                                                                                                $color =
                                                                                                                    'success';
                                                                                                                break;
                                                                                                            case 4:
                                                                                                                $color =
                                                                                                                    'danger';
                                                                                                                break;
                                                                                                            case 5:
                                                                                                                $color =
                                                                                                                    'info';
                                                                                                                break;
                                                                                                            default:
                                                                                                                $color =
                                                                                                                    'secondary';
                                                                                                                break;
                                                                                                        }
                                                                                                    @endphp

                                                                                                    <span
                                                                                                        class="badge badge-{{ $color }}">C{{ $clusterNum }}</span>
                                                                                                </td>

                                                                                                @foreach ($hasil['features'] as $f)
                                                                                                    <td>{{ number_format($centroid[$f] ?? 0, 4) }}
                                                                                                    </td>
                                                                                                @endforeach
                                                                                            </tr>
                                                                                        @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Menetapkan data ke kelas terdekat --}}
                                                            @if (!empty($hasil['allClusterResultsPerIteration']))
                                                                <div class="row mt-4">
                                                                    @php
                                                                        $finalIterIndex =
                                                                            count(
                                                                                $hasil['allClusterResultsPerIteration'],
                                                                            ) - 1;
                                                                        $finalClusters =
                                                                            $hasil['allClusterResultsPerIteration'][
                                                                                $finalIterIndex
                                                                            ] ?? [];
                                                                    @endphp

                                                                    @foreach ($finalClusters as $clusterIndex => $result)
                                                                        @php
                                                                            $clusterNumber =
                                                                                $result['cluster'] ?? $clusterIndex + 1;
                                                                            $clusterName = 'C' . $clusterNumber;
                                                                            $itemsText = $result['products'] ?? '';
                                                                            $items = array_filter(
                                                                                array_map(
                                                                                    'trim',
                                                                                    explode(',', $itemsText),
                                                                                ),
                                                                            );
                                                                        @endphp

                                                                        <div class="col-md-4 mb-2">
                                                                            <div class="card shadow-sm">
                                                                                <div class="card-header">
                                                                                    <h5 class="mb-0">
                                                                                        Cluster {{ $clusterNumber }}:
                                                                                        {{ $hasil['clusterLabel'][$clusterName] ?? 'Tidak Ada Label' }}
                                                                                    </h5>
                                                                                </div>

                                                                                <div class="card-body">
                                                                                    <div class="table-responsive">
                                                                                        <table
                                                                                            class="table table-sm table-bordered align-middle">
                                                                                            <thead class="table-light">
                                                                                                <tr>
                                                                                                    <th
                                                                                                        class="text-center">
                                                                                                        No</th>
                                                                                                    <th>Produk</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                @forelse ($items as $idx => $item)
                                                                                                    <tr>
                                                                                                        <td
                                                                                                            class="text-center">
                                                                                                            {{ $idx + 1 }}
                                                                                                        </td>
                                                                                                        <td>{{ $item }}
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @empty
                                                                                                    <tr>
                                                                                                        <td colspan="2"
                                                                                                            class="text-center">
                                                                                                            Tidak ada data
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endforelse
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            @if (!empty($hasil['silhouetteTable']))
                                                                @php
                                                                    $group = collect(
                                                                        $hasil['silhouetteTable'],
                                                                    )->groupBy('cluster');
                                                                    $k = $selectedCluster ?? 0;
                                                                @endphp

                                                                <div class="card mt-4 shadow-sm">
                                                                    <div
                                                                        class="card-header bg-info text-white text-center">
                                                                        <h4>Silhouette Coefficient</h4>
                                                                    </div>

                                                                    <div class="card-body">

                                                                        <div
                                                                            class="d-flex justify-content-start align-items-center p-2 mb-5  border rounded bg-light">
                                                                            <div>
                                                                                <div class="font-weight-semibold mr-2">
                                                                                    Average Overall:
                                                                                </div>
                                                                            </div>

                                                                            <span class="badge badge-primary">
                                                                                {{ number_format($hasil['averageSilhouetteOverall'], 3) }}
                                                                            </span>
                                                                        </div>




                                                                        <div class="table-responsive">
                                                                            <table
                                                                                class="table table-bordered table-sm text-center">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th rowspan="2">Cluster</th>
                                                                                        <th rowspan="2">Kode</th>
                                                                                        <th colspan="3">Data</th>
                                                                                        <th rowspan="2">a(i)</th>
                                                                                        @for ($i = 1; $i <= $k; $i++)
                                                                                            <th rowspan="2">
                                                                                                d(i,{{ $i }})
                                                                                            </th>
                                                                                        @endfor
                                                                                        <th rowspan="2">b(i)</th>
                                                                                        <th rowspan="2">S(i)</th>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Harga</th>
                                                                                        <th>Produk</th>
                                                                                        <th>Penjualan</th>
                                                                                    </tr>
                                                                                </thead>

                                                                                <tbody>
                                                                                    @foreach ($group as $cluster => $items)
                                                                                        @foreach ($items as $i => $row)
                                                                                            <tr>
                                                                                                @if ($i == 0)
                                                                                                    <td
                                                                                                        rowspan="{{ count($items) }}">
                                                                                                        C{{ $cluster }}
                                                                                                    </td>
                                                                                                @endif

                                                                                                <td>{{ $row['kode'] }}
                                                                                                </td>
                                                                                                <td>{{ number_format($row['harga'], 3) }}
                                                                                                </td>
                                                                                                <td>{{ number_format($row['total_product'], 3) }}
                                                                                                </td>
                                                                                                <td>{{ number_format($row['total_penjualan'], 3) }}
                                                                                                </td>
                                                                                                <td>{{ number_format($row['a'], 6) }}
                                                                                                </td>

                                                                                                @for ($i = 1; $i <= $k; $i++)
                                                                                                    <td>
                                                                                                        {{ $row['d'][$i] === null ? '-' : number_format($row['d'][$i], 6) }}
                                                                                                    </td>
                                                                                                @endfor

                                                                                                <td>{{ number_format($row['b'], 6) }}
                                                                                                </td>
                                                                                                <td>{{ number_format($row['s'], 6) }}
                                                                                                </td>
                                                                                            </tr>
                                                                                        @endforeach
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- Scatter --}}
                                                            @if (!empty($hasil['clusterScatterDatasets']) && !empty($hasil['centroidScatter']))
                                                                <div class="row mt-4">
                                                                    <div class="col-12">
                                                                        <div class="card shadow-sm">
                                                                            <div class="card-header">
                                                                                <h5 class="mb-0">
                                                                                    Visualisasi Clustering Akhir (Scatter)
                                                                                </h5>
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <div class="table-responsive">
                                                                                    <canvas
                                                                                        id="{{ $section['canvasId'] }}"
                                                                                        height="120"></canvas>
                                                                                </div>
                                                                                <small class="text-muted d-block mt-2">
                                                                                    Titik berwarna = data produk, titik
                                                                                    hitam = centroid.
                                                                                    Kedekatan titik menunjukkan kemiripan,
                                                                                    jarak centroid menunjukkan
                                                                                    separasi antar cluster.
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    @else
                                        <div class="card shadow-sm mt-4">
                                            <div class="card-body text-center text-muted">
                                                Belum ada hasil untuk {{ strtolower($section['title']) }}.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

<style>
    #clusterScatterChartMakanan,
    #clusterScatterChartMinuman {
        width: 100%;
        height: 400px;
    }
</style>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (function() {
            const clusterSelect = document.getElementById('cluster');

            function showBlock(k) {
                [2, 3, 4, 5].forEach(n => {
                    const block = document.getElementById('block-' + n);
                    if (!block) return;

                    block.style.display = 'none';

                    block.querySelectorAll('select').forEach(s => {
                        s.disabled = true;
                        s.removeAttribute('required');
                    });
                });

                const active = document.getElementById('block-' + k);
                if (active) {
                    active.style.display = 'block';

                    active.querySelectorAll('select').forEach(s => {
                        s.disabled = false;
                        s.setAttribute('required', 'required');
                    });
                }
            }

            @if (!empty($selectedCluster))
                showBlock({{ $selectedCluster }});
            @endif

            if (clusterSelect) {
                clusterSelect.addEventListener('change', function() {
                    if (this.value) {
                        showBlock(this.value);
                    }
                });
            }
        })();
    </script>

    <script>
        function initScatterChart(canvasId, datasetsData, centroidData, plotX, plotY) {
            const el = document.getElementById(canvasId);
            if (!el) return;

            const datasets = JSON.parse(JSON.stringify(datasetsData || []));
            const centroids = centroidData || [];

            datasets.push({
                label: 'Centroid',
                data: centroids.map(c => ({
                    x: c.x,
                    y: c.y,
                    cluster: c.cluster
                })),
                backgroundColor: 'rgba(0,0,0,0.75)',
                pointRadius: 7,
                pointStyle: 'rectRot',
            });

            new Chart(el.getContext('2d'), {
                type: 'scatter',
                data: {
                    datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const p = ctx.raw || {};

                                    if (p.cluster) {
                                        return `Centroid ${p.cluster}: (${Number(p.x).toFixed(2)}, ${Number(p.y).toFixed(2)})`;
                                    }

                                    const name = p.name || p.produk || 'Data';
                                    const id = p.id || '-';

                                    return `${name} [ID ${id}] → (${Number(p.x).toFixed(2)}, ${Number(p.y).toFixed(2)})`;
                                }
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: plotX || 'X'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: plotY || 'Y'
                            }
                        }
                    }
                }
            });
        }

        @if (!empty($hasilMakanan['clusterScatterDatasets']) && !empty($hasilMakanan['centroidScatter']))
            initScatterChart(
                'clusterScatterChartMakanan',
                @json($hasilMakanan['clusterScatterDatasets']),
                @json($hasilMakanan['centroidScatter']),
                @json($hasilMakanan['plotX']),
                @json($hasilMakanan['plotY'])
            );
        @endif

        @if (!empty($hasilMinuman['clusterScatterDatasets']) && !empty($hasilMinuman['centroidScatter']))
            initScatterChart(
                'clusterScatterChartMinuman',
                @json($hasilMinuman['clusterScatterDatasets']),
                @json($hasilMinuman['centroidScatter']),
                @json($hasilMinuman['plotX']),
                @json($hasilMinuman['plotY'])
            );
        @endif
    </script>
@endpush
