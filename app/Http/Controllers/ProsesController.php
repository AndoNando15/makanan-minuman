<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProsesController extends Controller
{
    public function index()
    {
        $totalDataset = Dataset::count();

        $totalMakanan = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['makanan'])->count();
        $totalMinuman = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['minuman'])->count();

        $allDatasetsMakanan = $this->orderedDatasetQuery()
            ->whereRaw('LOWER(kategori_barang) = ?', ['makanan'])
            ->select('id', 'kode', 'produk')
            ->get();

        $allDatasetsMinuman = $this->orderedDatasetQuery()
            ->whereRaw('LOWER(kategori_barang) = ?', ['minuman'])
            ->select('id', 'kode', 'produk')
            ->get();

        return view('pages.proses.index', [
            'totalDataset' => $totalDataset,
            'totalMakanan' => $totalMakanan,
            'totalMinuman' => $totalMinuman,
            'allDatasetsMakanan' => $allDatasetsMakanan,
            'allDatasetsMinuman' => $allDatasetsMinuman,
            'selectedCluster' => null,
            'hasilMakanan' => null,
            'hasilMinuman' => null,
        ]);
    }

    public function show($id)
    {
        return redirect()->route('proses.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'cluster' => 'required|in:2,3,4,5',
        ]);

        $k = (int) $request->cluster;
        $features = ['harga', 'total_product', 'total_penjualan'];

        $totalMakanan = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['makanan'])->count();
        $totalMinuman = Dataset::whereRaw('LOWER(kategori_barang) = ?', ['minuman'])->count();

        if ($k > $totalMakanan || $k > $totalMinuman) {
            throw ValidationException::withMessages([
                'cluster' => ['Jumlah cluster tidak boleh lebih besar dari jumlah data kategori makanan atau minuman.'],
            ]);
        }

        $hasilMakanan = $this->runKMeansPerCategory(
            'makanan',
            $k,
            $features
        );

        $hasilMinuman = $this->runKMeansPerCategory(
            'minuman',
            $k,
            $features
        );

        $totalDataset = Dataset::count();

        return view('pages.proses.index', [
            'totalDataset' => $totalDataset,
            'totalMakanan' => $totalMakanan,
            'totalMinuman' => $totalMinuman,
            'selectedCluster' => $k,
            'hasilMakanan' => $hasilMakanan,
            'hasilMinuman' => $hasilMinuman,
        ]);
    }

    private function runKMeansPerCategory(string $kategori, int $k, array $features): array
    {
        $kategori = strtolower($kategori);

        $points = $this->orderedDatasetQuery()
            ->whereRaw('LOWER(kategori_barang) = ?', [$kategori])
            ->get();

        if ($points->isEmpty()) {
            return $this->emptyKMeansResult($features);
        }

        if ($points->count() < $k) {
            throw ValidationException::withMessages([
                'cluster' => ['Jumlah cluster tidak boleh lebih besar dari jumlah data kategori ' . ucfirst($kategori) . '.'],
            ]);
        }

        $sortedBySales = $points->sortByDesc('total_penjualan')->values();
        $selectedIndices = $this->calculateInitialCentroidIndices($sortedBySales->count(), $k);

        $selectedIds = [];
        foreach ($selectedIndices as $index) {
            $selectedIds[] = $sortedBySales[$index]->id;
        }

        $selectedDatasetsRaw = [];
        foreach ($selectedIds as $id) {
            $item = $sortedBySales->firstWhere('id', $id);
            $selectedDatasetsRaw[] = [
                'id' => $item->id,
                'kode' => $item->kode,
                'produk' => $item->produk,
                'kategori_barang' => $item->kategori_barang,
                'harga' => (float) $item->harga,
                'total_product' => (float) $item->total_product,
                'total_penjualan' => (float) $item->total_penjualan,
            ];
        }

        $minMax = [];
        foreach ($features as $feature) {
            $minMax[$feature] = [
                'min' => (float) $points->min($feature),
                'max' => (float) $points->max($feature),
            ];
        }

        $X = [];
        $names = [];

        foreach ($points as $p) {
            $vec = [];

            foreach ($features as $f) {
                $rawValue = (float) ($p->{$f} ?? 0);
                $vec[$f] = $this->minMaxNormalize(
                    $rawValue,
                    $minMax[$f]['min'],
                    $minMax[$f]['max']
                );
            }

            $X[$p->id] = $vec;
            $names[$p->id] = $p->produk;
        }

        $centroids = [];
        foreach ($selectedIds as $id) {
            $centroids[] = $X[$id];
        }

        $normalizedSelectedDatasets = [];
        foreach ($selectedIds as $id) {
            $item = $sortedBySales->firstWhere('id', $id);
            $normalizedSelectedDatasets[] = [
                'id' => $id,
                'kode' => $item->kode,
                'produk' => $item->produk,
                'kategori_barang' => $item->kategori_barang,
                'harga' => $X[$id]['harga'] ?? 0,
                'total_product' => $X[$id]['total_product'] ?? 0,
                'total_penjualan' => $X[$id]['total_penjualan'] ?? 0,
            ];
        }
        $maxIterations = 100;
        $threshold = 1e-6;
        $iterationsUsed = 0;

        $clustersIds = array_fill(0, $k, []);
        $allIterations = [];
        $allDistancesPerIteration = [];
        $allClusterResultsPerIteration = [];
        $allSSEPerIteration = [];
        $previousAssignment = [];

        for ($iter = 1; $iter <= $maxIterations; $iter++) {
            $iterationsUsed = $iter;
            $clustersIds = array_fill(0, $k, []);

            foreach ($X as $pid => $vec) {
                $bestIdx = 0;
                $bestD2 = INF;

                foreach ($centroids as $idx => $cvec) {
                    $d2 = $this->squaredEuclideanVec($vec, $cvec, $features);

                    if ($d2 < $bestD2) {
                        $bestD2 = $d2;
                        $bestIdx = $idx;
                    }
                }

                $clustersIds[$bestIdx][] = $pid;
            }

            $newCentroids = [];

            foreach ($clustersIds as $idx => $members) {
                if (count($members) === 0) {
                    $newCentroids[$idx] = $centroids[$idx];
                    continue;
                }

                $sum = array_fill_keys($features, 0.0);

                foreach ($members as $pid) {
                    foreach ($features as $f) {
                        $sum[$f] += $X[$pid][$f];
                    }
                }

                $mean = [];
                foreach ($features as $f) {
                    $mean[$f] = $sum[$f] / count($members);
                }

                $newCentroids[$idx] = $mean;
            }

            $allIterations[] = [
                'iteration' => $iter,
                'centroids' => $newCentroids,
                'clusters' => $clustersIds,
            ];

            $sseIteration = 0.0;
            $distanceTableForThisIteration = [];
            $currentAssignment = [];

            foreach ($points as $p) {
                $vec = $X[$p->id];
                $dList = [];
                $bestIdx = 0;
                $bestD2 = INF;

                foreach ($centroids as $idx => $cvec) {
                    $d2 = $this->squaredEuclideanVec($vec, $cvec, $features);
                    $d = sqrt($d2);
                    $dList[] = $d;

                    if ($d2 < $bestD2) {
                        $bestD2 = $d2;
                        $bestIdx = $idx;
                    }
                }

                $sseIteration += $bestD2;

                $assignedCluster = $bestIdx + 1;
                $changed = 'Tidak';

                if (isset($previousAssignment[$p->id]) && $previousAssignment[$p->id] != $assignedCluster) {
                    $changed = 'Iya';
                }

                $currentAssignment[$p->id] = $assignedCluster;

                $distanceTableForThisIteration[] = [
                    'dataset' => $p,
                    'distances' => $dList,
                    'nearest' => $assignedCluster,
                    'dmin' => sqrt($bestD2),
                    'dminSquared' => $bestD2,
                    'changed' => $changed,
                ];
            }

            $allDistancesPerIteration[] = $distanceTableForThisIteration;
            $allSSEPerIteration[] = $sseIteration;
            $previousAssignment = $currentAssignment;

            $clusterResultsForThisIteration = [];
            foreach ($clustersIds as $idx => $members) {
                $clusterResultsForThisIteration[] = [
                    'cluster' => $idx + 1,
                    'products' => implode(', ', array_map(fn($id) => $names[$id], $members)),
                ];
            }
            $allClusterResultsPerIteration[] = $clusterResultsForThisIteration;

            $maxShift = 0.0;

            for ($i = 0; $i < $k; $i++) {
                $shift = sqrt($this->squaredEuclideanVec($centroids[$i], $newCentroids[$i], $features));
                if ($shift > $maxShift) {
                    $maxShift = $shift;
                }
            }

            $centroids = $newCentroids;

            if ($maxShift < $threshold) {
                break;
            }
        }

        $totalSSE = array_sum($allSSEPerIteration);

        $distanceTable = [];
        $sseTotal = 0.0;

        foreach ($points as $p) {
            $vec = $X[$p->id];
            $dList = [];
            $bestIdx = 0;
            $bestD2 = INF;

            foreach ($centroids as $idx => $cvec) {
                $d2 = $this->squaredEuclideanVec($vec, $cvec, $features);
                $d = sqrt($d2);
                $dList[] = $d;

                if ($d2 < $bestD2) {
                    $bestD2 = $d2;
                    $bestIdx = $idx;
                }
            }

            $sseTotal += $bestD2;

            $distanceTable[] = [
                'dataset' => $p,
                'distances' => $dList,
                'nearest' => $bestIdx + 1,
                'dmin' => sqrt($bestD2),
                'dminSquared' => $bestD2,
            ];
        }

        $clusterResults = [];
        foreach ($clustersIds as $idx => $members) {
            $clusterResults[] = [
                'cluster' => $idx + 1,
                'products' => implode(', ', array_map(fn($id) => $names[$id], $members)),
            ];
        }

        $newCentroids = $centroids;

        $centroidAverages = [];
        foreach ($newCentroids as $index => $centroid) {
            $clusterName = 'C' . ($index + 1);
            $centroidAverages[$clusterName] = array_sum($centroid) / max(count($centroid), 1);
        }

        $sorted = $centroidAverages;
        arsort($sorted);

        $rankLabels = [
            1 => 'Penjualan Sangat Tinggi',
            2 => 'Penjualan Tinggi',
            3 => 'Penjualan Sedang',
            4 => 'Penjualan Rendah',
            5 => 'Penjualan Sangat Rendah',
        ];

        $rankColors = [
            1 => 'primary',
            2 => 'warning',
            3 => 'success',
            4 => 'danger',
            5 => 'info',
        ];

        $clusterRank = [];
        $clusterLabel = [];
        $clusterColor = [];

        $rank = 1;
        foreach ($sorted as $clusterName => $avg) {
            $clusterRank[$clusterName] = $rank;
            $clusterLabel[$clusterName] = $rankLabels[$rank] ?? 'Tidak Ada Data';
            $clusterColor[$clusterName] = $rankColors[$rank] ?? 'secondary';
            $rank++;
        }

        $centroidSum = [];
        foreach ($newCentroids as $index => $centroid) {
            $clusterName = 'C' . ($index + 1);
            $centroidSum[$clusterName] = array_sum($centroid);
        }

        $featureVariance = [];
        foreach ($features as $f) {
            $vals = [];
            foreach ($X as $pid => $vec) {
                $vals[] = (float) ($vec[$f] ?? 0);
            }

            $mean = array_sum($vals) / max(count($vals), 1);
            $var = 0.0;

            foreach ($vals as $v) {
                $var += ($v - $mean) * ($v - $mean);
            }

            $featureVariance[$f] = $var / max(count($vals), 1);
        }

        arsort($featureVariance);
        $top = array_keys($featureVariance);

        $plotX = $top[0] ?? $features[0];
        $plotY = $top[1] ?? ($features[1] ?? $features[0]);

        $clusterScatterDatasets = [];
        $clusterColors = [
            0 => 'rgba(13,110,253,0.65)',
            1 => 'rgba(255,193,7,0.65)',
            2 => 'rgba(25,135,84,0.65)',
            3 => 'rgba(220,53,69,0.65)',
            4 => 'rgba(13,202,240,0.65)',
        ];

        foreach ($clustersIds as $idx => $members) {
            $pts = [];

            foreach ($members as $pid) {
                $pts[] = [
                    'x' => (float) ($X[$pid][$plotX] ?? 0),
                    'y' => (float) ($X[$pid][$plotY] ?? 0),
                    'name' => $names[$pid] ?? ('ID ' . $pid),
                    'id' => $pid,
                ];
            }

            $clusterScatterDatasets[] = [
                'label' => 'C' . ($idx + 1),
                'data' => $pts,
                'backgroundColor' => $clusterColors[$idx] ?? 'rgba(108,117,125,0.65)',
                'pointRadius' => 4,
            ];
        }

        $centroidScatter = [];
        foreach ($newCentroids as $idx => $c) {
            $centroidScatter[] = [
                'x' => (float) ($c[$plotX] ?? 0),
                'y' => (float) ($c[$plotY] ?? 0),
                'cluster' => 'C' . ($idx + 1),
            ];
        }

        $finalCentroids = array_values(array_map(function ($c) {
            return isset($c['centroid']) ? array_values($c['centroid']) : array_values($c);
        }, $centroids));

        $dbiPerCentroid = $this->calculateDBIPerCentroid($finalCentroids);
        $silhouette = $this->calculateSilhouetteDetailed($points, $X, $clustersIds, $features);
        return [
            'kategori' => $kategori,
            'selectedDatasets' => $selectedDatasetsRaw,
            'distanceTable' => $distanceTable,
            'normalizedSelectedDatasets' => $normalizedSelectedDatasets,
            'features' => $features,
            'clusterResults' => $clusterResults,
            'sseTotal' => $sseTotal,
            'newCentroids' => $newCentroids,
            'centroidAverages' => $centroidAverages,
            'dbiPerCentroid' => $dbiPerCentroid,
            'centroidSum' => $centroidSum,
            'allIterations' => $allIterations,
            'allDistancesPerIteration' => $allDistancesPerIteration,
            'clusterRank' => $clusterRank,
            'clusterLabel' => $clusterLabel,
            'clusterColor' => $clusterColor,
            'plotX' => $plotX,
            'plotY' => $plotY,
            'clusterScatterDatasets' => $clusterScatterDatasets,
            'centroidScatter' => $centroidScatter,
            'allClusterResultsPerIteration' => $allClusterResultsPerIteration,
            'allSSEPerIteration' => $allSSEPerIteration,
            'totalSSE' => $totalSSE,
            'iterationsUsed' => $iterationsUsed,
            'minMax' => $minMax,
            'silhouetteTable' => $silhouette['rows'] ?? [],
            'averageSilhouettePerCluster' => $silhouette['perCluster'] ?? [],
            'averageSilhouetteOverall' => $silhouette['overall'] ?? 0,
        ];
    }
    private function calculateInitialCentroidIndices(int $count, int $k): array
    {
        if ($k <= 1) {
            return $count > 0 ? [0] : [];
        }

        $indices = [];
        for ($i = 0; $i < $k; $i++) {
            $indices[] = (int) floor($i * ($count - 1) / ($k - 1));
        }

        return array_values(array_unique($indices));
    }

    private function calculateSilhouetteDetailed($points, array $X, array $clustersIds, array $features): array
    {
        $clusterMap = [];

        foreach ($clustersIds as $idx => $members) {
            foreach ($members as $pid) {
                $clusterMap[$pid] = $idx + 1;
            }
        }

        $rows = [];
        $clusterSums = [];
        $clusterCounts = [];
        $overallSum = 0;
        $overallCount = 0;

        foreach ($points as $p) {
            $pid = $p->id;
            $ownCluster = $clusterMap[$pid] ?? 1;
            $ownIndex = $ownCluster - 1;
            $ownMembers = $clustersIds[$ownIndex] ?? [];

            // a(i)
            if (count($ownMembers) <= 1) {
                $a = 0;
            } else {
                $sumA = 0;
                $countA = 0;

                foreach ($ownMembers as $m) {
                    if ($m == $pid)
                        continue;
                    $sumA += $this->euclideanVec($X[$pid], $X[$m], $features);
                    $countA++;
                }

                $a = $countA ? $sumA / $countA : 0;
            }

            // d(i,j) & b(i)
            $distances = [];
            $b = INF;

            foreach ($clustersIds as $idx => $members) {
                $clusterNo = $idx + 1;

                if ($idx == $ownIndex || empty($members)) {
                    $distances[$clusterNo] = null;
                    continue;
                }

                $sumD = 0;
                $countD = 0;

                foreach ($members as $m) {
                    $sumD += $this->euclideanVec($X[$pid], $X[$m], $features);
                    $countD++;
                }

                $avg = $countD ? $sumD / $countD : null;
                $distances[$clusterNo] = $avg;

                if ($avg !== null && $avg < $b) {
                    $b = $avg;
                }
            }

            if ($b === INF)
                $b = 0;

            // S(i)
            $den = max($a, $b);
            $s = $den > 0 ? ($b - $a) / $den : 0;

            $rows[] = [
                'cluster' => $ownCluster,
                'kode' => $p->kode,
                'produk' => $p->produk,
                'harga' => $X[$pid]['harga'] ?? 0,
                'total_product' => $X[$pid]['total_product'] ?? 0,
                'total_penjualan' => $X[$pid]['total_penjualan'] ?? 0,
                'a' => $a,
                'd' => $distances,
                'b' => $b,
                's' => $s,
            ];

            $clusterSums[$ownCluster] = ($clusterSums[$ownCluster] ?? 0) + $s;
            $clusterCounts[$ownCluster] = ($clusterCounts[$ownCluster] ?? 0) + 1;

            $overallSum += $s;
            $overallCount++;
        }

        // SORT
        usort($rows, function ($a, $b) {
            if ($a['cluster'] != $b['cluster']) {
                return $a['cluster'] <=> $b['cluster'];
            }

            preg_match('/^([A-Za-z]+)(\d+)$/', $a['kode'], $matchA);
            preg_match('/^([A-Za-z]+)(\d+)$/', $b['kode'], $matchB);

            $prefixA = $matchA[1] ?? $a['kode'];
            $numberA = isset($matchA[2]) ? (int) $matchA[2] : 0;

            $prefixB = $matchB[1] ?? $b['kode'];
            $numberB = isset($matchB[2]) ? (int) $matchB[2] : 0;

            if ($prefixA !== $prefixB) {
                return strcmp($prefixA, $prefixB);
            }

            return $numberA <=> $numberB;
        });

        // AVG PER CLUSTER
        $perCluster = [];
        foreach ($clusterSums as $c => $sum) {
            $perCluster[] = [
                'cluster' => $c,
                'average' => $clusterCounts[$c] ? $sum / $clusterCounts[$c] : 0,
                'count' => $clusterCounts[$c],
            ];
        }

        $overall = $overallCount ? $overallSum / $overallCount : 0;

        return [
            'rows' => $rows,
            'perCluster' => $perCluster,
            'overall' => $overall,
        ];
    }
    private function emptyKMeansResult(array $features): array
    {
        return [
            'kategori' => null,
            'selectedDatasets' => [],
            'distanceTable' => [],
            'features' => $features,
            'clusterResults' => [],
            'sseTotal' => 0,
            'newCentroids' => [],
            'centroidAverages' => [],
            'dbiPerCentroid' => [],
            'centroidSum' => [],
            'allIterations' => [],
            'allDistancesPerIteration' => [],
            'clusterRank' => [],
            'clusterLabel' => [],
            'clusterColor' => [],
            'plotX' => null,
            'plotY' => null,
            'clusterScatterDatasets' => [],
            'centroidScatter' => [],
            'allClusterResultsPerIteration' => [],
            'allSSEPerIteration' => [],
            'totalSSE' => 0,
            'iterationsUsed' => 0,
            'minMax' => [],
            'silhouetteTable' => [],
            'averageSilhouettePerCluster' => [],
            'averageSilhouetteOverall' => 0,
        ];
    }

    private function orderedDatasetQuery()
    {
        return Dataset::query()
            ->orderByRaw('LEFT(kode, 1) ASC')
            ->orderByRaw('CAST(SUBSTRING(kode, 2) AS UNSIGNED) ASC');
    }

    private function minMaxNormalize($value, $min, $max): float
    {
        if ($max == $min) {
            return 0.0;
        }

        return ($value - $min) / ($max - $min);
    }

    private function squaredEuclideanVec(array $a, array $b, array $features): float
    {
        $sum = 0.0;

        foreach ($features as $f) {
            $xa = (float) ($a[$f] ?? 0);
            $xb = (float) ($b[$f] ?? 0);
            $d = $xa - $xb;
            $sum += $d * $d;
        }

        return $sum;
    }
    private function euclideanVec(array $a, array $b, array $features): float
    {
        return sqrt($this->squaredEuclideanVec($a, $b, $features));
    }

    private function calculateSilhouette($points, array $X, array $clustersIds, array $features): array
    {
        $clusterMap = [];

        foreach ($clustersIds as $idx => $members) {
            foreach ($members as $pid) {
                $clusterMap[$pid] = $idx + 1;
            }
        }

        $details = [];
        $clusterSums = [];
        $clusterCounts = [];
        $overallSum = 0.0;
        $overallCount = 0;

        foreach ($points as $p) {
            $pid = $p->id;
            $ownClusterNumber = $clusterMap[$pid] ?? 1;
            $ownClusterIndex = $ownClusterNumber - 1;
            $ownMembers = $clustersIds[$ownClusterIndex] ?? [];

            // a(i): rata-rata jarak ke anggota cluster sendiri
            if (count($ownMembers) <= 1) {
                $a = 0.0;
            } else {
                $sumA = 0.0;
                $countA = 0;

                foreach ($ownMembers as $memberId) {
                    if ($memberId == $pid) {
                        continue;
                    }

                    $sumA += $this->euclideanVec($X[$pid], $X[$memberId], $features);
                    $countA++;
                }

                $a = $countA > 0 ? $sumA / $countA : 0.0;
            }

            // b(i): minimum rata-rata jarak ke cluster lain
            $b = INF;

            foreach ($clustersIds as $idx => $members) {
                if ($idx == $ownClusterIndex || count($members) === 0) {
                    continue;
                }

                $sumB = 0.0;
                $countB = 0;

                foreach ($members as $memberId) {
                    $sumB += $this->euclideanVec($X[$pid], $X[$memberId], $features);
                    $countB++;
                }

                $avgB = $countB > 0 ? $sumB / $countB : INF;

                if ($avgB < $b) {
                    $b = $avgB;
                }
            }

            if ($b === INF) {
                $b = 0.0;
            }

            $denominator = max($a, $b);
            $s = $denominator > 0 ? (($b - $a) / $denominator) : 0.0;

            $details[] = [
                'id' => $p->id,
                'kode' => $p->kode,
                'produk' => $p->produk,
                'cluster' => $ownClusterNumber,
                'a' => $a,
                'b' => $b,
                's' => $s,
            ];

            if (!isset($clusterSums[$ownClusterNumber])) {
                $clusterSums[$ownClusterNumber] = 0.0;
                $clusterCounts[$ownClusterNumber] = 0;
            }

            $clusterSums[$ownClusterNumber] += $s;
            $clusterCounts[$ownClusterNumber]++;

            $overallSum += $s;
            $overallCount++;
        }

        $perCluster = [];
        ksort($clusterSums);

        foreach ($clusterSums as $cluster => $sum) {
            $perCluster[] = [
                'cluster' => $cluster,
                'average' => $clusterCounts[$cluster] > 0 ? $sum / $clusterCounts[$cluster] : 0.0,
                'count' => $clusterCounts[$cluster],
            ];
        }

        $overall = $overallCount > 0 ? $overallSum / $overallCount : 0.0;

        return [
            'details' => $details,
            'perCluster' => $perCluster,
            'overall' => $overall,
        ];
    }
    public function calculateDBIPerCentroid($centroids)
    {
        $result = [];
        $count = count($centroids);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $sumSq = 0;
                $dim = count($centroids[$i]);

                for ($k = 0; $k < $dim; $k++) {
                    $sumSq += pow($centroids[$i][$k] - $centroids[$j][$k], 2);
                }

                $distance = sqrt($sumSq);

                $result[] = [
                    'pair' => 'C' . ($i + 1) . ' - C' . ($j + 1),
                    'without_sqrt' => $sumSq,
                    'euclidean' => $distance,
                ];
            }
        }

        return $result;
    }
}