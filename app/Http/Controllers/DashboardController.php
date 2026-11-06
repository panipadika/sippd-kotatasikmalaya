<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\KelompokUrusan;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\Tahun;
use App\Models\DataIndikator;
use App\Models\ActivityLog;
use App\Services\DataKelengkapanEvaluator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tentukan tahun terpilih: prioritaskan parameter URL, fallback ke sesi / default aktif
        [$tahun, $selectedTahunId, $selectedTahunNomor] = $this->resolveSelectedTahun($request);

        // 2. Statistik Master Data:
        $totalKelompok = KelompokUrusan::whereNull('parent_id')->count();
        $totalUrusan = KelompokUrusan::whereNotNull('parent_id')->count();
        $totalIndikator = Urusan::where('status_aktif', true)->count();
        
        $leafIndikators = Indikator::where('status_aktif', true)
            ->doesntHave('children')
            ->with('urusan')
            ->get();
        $totalRincianData = $leafIndikators->count();

        $allDataIndikator = DataIndikator::with('tahun')->get();

        // 3. Kelengkapan Data Nilai pada Tahun Terpilih (Evaluasi Cerdas & Realtime)
        $totalDataTerisi = 0;
        $filledUrusanMap = [];
        $leafCountsMap = [];

        foreach ($leafIndikators as $leaf) {
            $uId = (int) $leaf->urusan_id;
            $leafCountsMap[$uId] = ($leafCountsMap[$uId] ?? 0) + 1;

            if (DataKelengkapanEvaluator::isFilledForYear($leaf, (int) $selectedTahunNomor, $allDataIndikator)) {
                $totalDataTerisi++;
                $filledUrusanMap[$uId] = ($filledUrusanMap[$uId] ?? 0) + 1;
            }
        }

        $persentaseKelengkapan = $totalRincianData > 0 ? round(($totalDataTerisi / $totalRincianData) * 100, 1) : 0;
        if ($persentaseKelengkapan > 100) {
            $persentaseKelengkapan = 100;
        }

        // 4. Statistik Per Kelompok Urusan Utama (Kategori A s.d. H)
        $allKelompok = KelompokUrusan::with(['urusans' => function ($q) {
            $q->where('status_aktif', true);
        }])->orderBy('urutan')->get();

        $rootKelompoks = $allKelompok->whereNull('parent_id');
        $childrenByParent = $allKelompok->whereNotNull('parent_id')->groupBy('parent_id');

        $kelompokStats = $this->buildKelompokStats($rootKelompoks, $childrenByParent, $leafCountsMap, $filledUrusanMap);

        // 5. Tren kelengkapan data per tahun (hanya tahun-tahun aktif sistem)
        $semuaTahun = Tahun::syncActiveYearsFromSystem()->sortBy('tahun');
        $chartTahuns = [];
        $chartDataCounts = [];

        foreach ($semuaTahun as $t) {
            $yNum = (int) $t->tahun;
            $chartTahuns[] = (string) $yNum;
            $yCount = 0;
            foreach ($leafIndikators as $leaf) {
                if (DataKelengkapanEvaluator::isFilledForYear($leaf, $yNum, $allDataIndikator)) {
                    $yCount++;
                }
            }
            $chartDataCounts[] = $yCount;
        }

        // 6. Data nilai terakhir diperbarui & log aktivitas
        $recentUpdates = $this->getRecentUpdates();
        $recentActivities = ActivityLog::with('user')->latest('created_at')->take(8)->get();

        if (!$request->header('X-SPA-Request') && ($request->has('realtime') || ($request->ajax() && $request->wantsJson()))) {
            return response()->json([
                'success' => true,
                'selectedTahunNomor' => $selectedTahunNomor,
                'totalKelompok' => $totalKelompok,
                'totalUrusan' => $totalUrusan,
                'totalIndikator' => $totalIndikator,
                'totalRincianData' => $totalRincianData,
                'totalDataTerisi' => $totalDataTerisi,
                'persentaseKelengkapan' => $persentaseKelengkapan,
                'kelompokStats' => $kelompokStats->values(),
                'chartTahuns' => $chartTahuns,
                'chartDataCounts' => $chartDataCounts,
                'recentUpdates' => $this->formatRecentUpdates($recentUpdates),
                'listTahuns' => Tahun::syncActiveYearsFromSystem()->map(fn($t) => ['id' => $t->id, 'tahun' => $t->tahun, 'is_default' => $t->is_default]),
            ]);
        }

        return view('dashboard.index', compact(
            'selectedTahunNomor',
            'tahun',
            'totalKelompok',
            'totalUrusan',
            'totalIndikator',
            'totalRincianData',
            'totalDataTerisi',
            'persentaseKelengkapan',
            'kelompokStats',
            'chartTahuns',
            'chartDataCounts',
            'recentUpdates',
            'recentActivities'
        ));
    }

    /**
     * Resolves the selected year from request, session, or default.
     *
     * @param Request $request
     * @return array{0: ?Tahun, 1: ?int, 2: int}
     */
    private function resolveSelectedTahun(Request $request): array
    {
        if ($request->has('tahun')) {
            $tahun = Tahun::where('status_aktif', true)->where('tahun', (int) $request->get('tahun'))->first();
        } else {
            $sessTahun = session('selected_tahun');
            $tahun = $sessTahun ? Tahun::where('status_aktif', true)->where('tahun', (int) $sessTahun)->first() : null;
        }

        if (!$tahun) {
            $tahun = Tahun::where('status_aktif', true)->where('is_default', true)->first()
                ?: Tahun::where('status_aktif', true)->orderByDesc('tahun')->first();
        }

        if ($tahun) {
            session(['selected_tahun' => $tahun->tahun]);
            return [$tahun, $tahun->id, $tahun->tahun];
        }

        return [null, null, Tahun::getDefaultTahun() ?: 2026];
    }

    /**
     * Builds hierarchical statistics for each main kelompok urusan.
     *
     * @param Collection $rootKelompoks
     * @param Collection $childrenByParent
     * @param array<int, int> $leafCountsMap
     * @param array<int, int> $filledUrusanMap
     * @return Collection
     */
    private function buildKelompokStats(Collection $rootKelompoks, Collection $childrenByParent, array $leafCountsMap, array $filledUrusanMap): Collection
    {
        return $rootKelompoks->map(function ($root) use ($childrenByParent, $leafCountsMap, $filledUrusanMap) {
            $children = $childrenByParent->get($root->id, collect());

            $daftarUrusan = $children->map(function ($u) use ($leafCountsMap, $filledUrusanMap) {
                $indikators = $u->urusans;
                $indikatorIds = $indikators->pluck('id');

                $rincianCount = $indikatorIds->sum(fn($id) => $leafCountsMap[(int)$id] ?? 0);
                $terisi = $indikatorIds->sum(fn($id) => $filledUrusanMap[(int)$id] ?? 0);
                $pct = $rincianCount > 0 ? round(($terisi / $rincianCount) * 100, 1) : 0;

                $daftarIndikator = $indikators->map(function ($ind) use ($leafCountsMap, $filledUrusanMap) {
                    $rCount = $leafCountsMap[(int)$ind->id] ?? 0;
                    $tCount = $filledUrusanMap[(int)$ind->id] ?? 0;
                    $indPct = $rCount > 0 ? round(($tCount / $rCount) * 100, 1) : 0;

                    return [
                        'id' => $ind->id,
                        'nama' => $ind->nama_urusan,
                        'slug' => $ind->slug ?: $ind->id,
                        'total_rincian' => $rCount,
                        'total_terisi' => $tCount,
                        'persentase' => min($indPct, 100),
                    ];
                })->toArray();

                return [
                    'id' => $u->id,
                    'kode' => $u->kode,
                    'nama' => $u->nama,
                    'total_indikator' => $indikators->count(),
                    'daftar_indikator' => $daftarIndikator,
                    'total_rincian' => $rincianCount,
                    'total_terisi' => $terisi,
                    'persentase' => min($pct, 100),
                ];
            })->values();

            $allIndikatorIds = $children->flatMap(fn($c) => $c->urusans->pluck('id'))->unique();
            $totalIndikatorKel = $allIndikatorIds->count();

            $totalRincianKel = $allIndikatorIds->sum(fn($id) => $leafCountsMap[(int)$id] ?? 0);
            $terisiKel = $allIndikatorIds->sum(fn($id) => $filledUrusanMap[(int)$id] ?? 0);
            $pctKel = $totalRincianKel > 0 ? round(($terisiKel / $totalRincianKel) * 100, 1) : 0;

            return [
                'id' => $root->id,
                'kode' => $root->kode,
                'nama' => $root->nama,
                'total_urusan' => $children->count(),
                'total_indikator' => $totalIndikatorKel,
                'total_rincian' => $totalRincianKel,
                'total_terisi' => $terisiKel,
                'persentase' => min($pctKel, 100),
                'daftar_urusan' => $daftarUrusan,
            ];
        })->values();
    }

    /**
     * Retrieves recent updates filtered by real values.
     *
     * @return Collection
     */
    private function getRecentUpdates(): Collection
    {
        return DataIndikator::with(['indikator.urusan', 'tahun', 'updatedByUser'])
            ->where(function ($q) {
                $q->whereNotNull('nilai')->where('nilai', '!=', '')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('custom_fields')
                            ->where('custom_fields', '!=', '')
                            ->where('custom_fields', '!=', '[]')
                            ->where('custom_fields', '!=', '{}')
                            ->where('custom_fields', '!=', 'null');
                    });
            })
            ->latest('updated_at')
            ->get()
            ->filter(function ($upd) {
                if (DataKelengkapanEvaluator::isValueFilled($upd->nilai)) {
                    return true;
                }
                $cf = $upd->custom_fields ?? [];
                if (!is_array($cf)) {
                    return false;
                }
                foreach ($cf as $k => $v) {
                    if (DataKelengkapanEvaluator::isValueFilled($v)) {
                        return true;
                    }
                }
                return false;
            })
            ->take(8)
            ->values();
    }

    /**
     * Formats recent updates for JSON response.
     *
     * @param Collection $recentUpdates
     * @return Collection
     */
    private function formatRecentUpdates(Collection $recentUpdates): Collection
    {
        return $recentUpdates->map(function ($upd) {
            return [
                'id' => $upd->id,
                'urusan' => $upd->indikator && $upd->indikator->urusan ? $upd->indikator->urusan->nama_urusan : 'Indikator Sektoral',
                'slug' => $upd->indikator && $upd->indikator->urusan ? ($upd->indikator->urusan->slug ?: $upd->indikator->urusan->id) : '',
                'indikator' => $upd->indikator ? $upd->indikator->nama_indikator : '-',
                'tahun' => $upd->tahun ? $upd->tahun->tahun : '-',
                'nilai' => $upd->formatted_nilai,
                'satuan' => $upd->indikator ? ($upd->indikator->satuan ?? '') : '',
                'user' => $upd->updatedByUser ? $upd->updatedByUser->name : 'Administrator Data Sektoral',
                'time_ago' => $upd->updated_at ? $upd->updated_at->diffForHumans() : '-',
            ];
        });
    }
}
