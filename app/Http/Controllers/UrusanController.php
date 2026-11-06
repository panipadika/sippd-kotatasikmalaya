<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KelompokUrusan;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\Tahun;
use App\Models\DataIndikator;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ExcelService;

class UrusanController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('dashboard');
    }

    public function show(Request $request, $id)
    {
        // Accept either slug or numeric ID. Always try slug first so urusan with
        // numeric names (e.g. "1", "2323") whose slug is also numeric are found correctly.
        $urusan = Urusan::with('kelompokUrusan')->where('slug', $id)->first()
            ?? (is_numeric($id) ? Urusan::with('kelompokUrusan')->findOrFail($id) : null);

        if (!$urusan) {
            abort(404);
        }

        // Multi-table resolution
        $tables = $urusan->tables;

        // Cari tahu tahun yang terikat pada masing-masing tabel (halaman)
        $tableYearMap = []; // [tahun => table_id]
        foreach ($tables as &$tbl) {
            $tblId = (int) ($tbl['id'] ?? 1);
            $tblYr = $tbl['tahun'] ?? null;

            // 1. Ekstrak tahun dari kolom dan sub-kolom tabel ini secara rekursif
            $tblCols = $tbl['columns'] ?? [];
            $colYears = [];
            $extractColYears = function($cols) use (&$extractColYears, &$colYears) {
                foreach ($cols as $c) {
                    $cName = trim($c['name'] ?? '');
                    if (preg_match('/^(19|20)\d{2}$/', $cName, $m)) {
                        $colYears[] = (int) $m[0];
                    }
                    if (!empty($c['sub_columns']) && is_array($c['sub_columns'])) {
                        $extractColYears($c['sub_columns']);
                    }
                }
            };
            $extractColYears($tblCols);
            $colYears = array_values(array_unique($colYears));
            sort($colYears);

            if (!empty($colYears)) {
                $minYr = min($colYears);
                $maxYr = max($colYears);
                $tbl['tahun_range'] = ($minYr === $maxYr) ? (string)$minYr : "{$minYr} - {$maxYr}";
                $tbl['tahun'] = $maxYr;
                $tblYr = $maxYr;
                foreach ($colYears as $cy) {
                    if (!isset($tableYearMap[$cy])) {
                        $tableYearMap[$cy] = $tblId;
                    }
                }
            } else {
                // 2. Cek apakah ada tahun di nama indikator (tabel vertikal)
                $tblIndYears = Indikator::where('urusan_id', $urusan->id)
                    ->where(function ($q) use ($tblId) {
                        if ($tblId === 1) $q->where('table_id', 1)->orWhereNull('table_id');
                        else $q->where('table_id', $tblId);
                    })
                    ->pluck('nama_indikator')
                    ->filter(fn($n) => preg_match('/^(19|20)\d{2}$/', trim($n)))
                    ->map(fn($n) => (int)trim($n))
                    ->toArray();

                if (!empty($tblIndYears)) {
                    $minYr = min($tblIndYears);
                    $maxYr = max($tblIndYears);
                    $tbl['tahun_range'] = ($minYr === $maxYr) ? (string)$minYr : "{$minYr} - {$maxYr}";
                    $tbl['tahun'] = $maxYr;
                    $tblYr = $maxYr;
                    foreach ($tblIndYears as $iy) {
                        if (!isset($tableYearMap[$iy])) {
                            $tableYearMap[$iy] = $tblId;
                        }
                    }
                }
            }

            if (!$tblYr && preg_match('/(19|20)\d{2}/', $tbl['name'] ?? '', $mYr)) {
                $tblYr = (int) $mYr[0];
            }
            if (!$tblYr) {
                $firstInd = Indikator::where('urusan_id', $urusan->id)
                    ->where(function ($q) use ($tblId) {
                        if ($tblId === 1) $q->where('table_id', 1)->orWhereNull('table_id');
                        else $q->where('table_id', $tblId);
                    })->first();
                if ($firstInd) {
                    $firstDi = $firstInd->dataIndikators()->with('tahun')->latest()->first();
                    if ($firstDi && $firstDi->tahun) {
                        $tblYr = (int) $firstDi->tahun->tahun;
                    }
                }
            }
            $tblYr = $tblYr ? (int) $tblYr : (int) date('Y');
            $tbl['tahun'] = $tblYr;
            if (empty($tbl['tahun_range'])) {
                $tbl['tahun_range'] = (string) $tblYr;
            }
            if (!isset($tableYearMap[$tblYr])) {
                $tableYearMap[$tblYr] = $tblId;
            }
        }
        unset($tbl);

        // Tentukan activeTableId: jika ada query param tabel, gunakan itu.
        // Jika ada filter tahun dan cocok dengan halaman tabel tertentu, arahkan ke tabel tersebut.
        if ($request->has('tabel')) {
            $activeTableId = (int) $request->get('tabel');
        } elseif ($request->has('tahun') && isset($tableYearMap[(int) $request->get('tahun')])) {
            $activeTableId = (int) $tableYearMap[(int) $request->get('tahun')];
        } else {
            $activeTableId = (int) ($tables[0]['id'] ?? 1);
        }

        $activeTable = $urusan->getTableConfig($activeTableId);
        $allColumns = $activeTable['columns'] ?? [];

        // 1. Extract 4-digit years from existing table columns and nested sub-columns
        $tableYearColumns = [];
        $extractYears = function($cols) use (&$extractYears, &$tableYearColumns) {
            foreach ($cols as $col) {
                $colName = trim($col['name'] ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $colName, $m)) {
                    $tableYearColumns[] = (int) $m[0];
                }
                if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                    $extractYears($col['sub_columns']);
                }
            }
        };
        $extractYears($allColumns);

        // 2. Extract years from indicators / rows of this active table
        $activeIndikatorIds = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->pluck('id');

        $rowYears = DataIndikator::whereIn('indikator_id', $activeIndikatorIds)
            ->join('tahun', 'data_indikator.tahun_id', '=', 'tahun.id')
            ->pluck('tahun.tahun')
            ->map(fn($y) => (int)$y)
            ->toArray();

        $nameYears = Indikator::whereIn('id', $activeIndikatorIds)
            ->pluck('nama_indikator')
            ->filter(fn($n) => preg_match('/^(19|20)\d{2}$/', trim($n)))
            ->map(fn($n) => (int)trim($n))
            ->toArray();

        $hasYearColumns = !empty($tableYearColumns);
        $hasYearRows = !$hasYearColumns && !empty($nameYears);

        $activeTableYear = null;
        foreach ($tables as &$t) {
            if ((int)($t['id'] ?? 1) === $activeTableId) {
                if ($hasYearColumns) {
                    $minYr = min($tableYearColumns);
                    $maxYr = max($tableYearColumns);
                    $t['tahun_range'] = ($minYr === $maxYr) ? (string)$minYr : "{$minYr} - {$maxYr}";
                    $activeTableYear = $maxYr;
                } elseif ($hasYearRows) {
                    $minYr = min($nameYears);
                    $maxYr = max($nameYears);
                    $t['tahun_range'] = ($minYr === $maxYr) ? (string)$minYr : "{$minYr} - {$maxYr}";
                    $activeTableYear = $minYr;
                } else {
                    $activeTableYear = (int) ($t['tahun'] ?? date('Y'));
                }
                break;
            }
        }
        unset($t);

        // Available years for dropdowns:
        // Jika tabel memiliki kolom-kolom tahun (misal 2024, 2025, 2026),
        // maka filter rentang tahun HANYA menampilkan tahun yang ada pada kolom tersebut.
        if ($hasYearColumns) {
            $allAvailableYears = array_values(array_unique($tableYearColumns));
            sort($allAvailableYears);
        } elseif ($hasYearRows) {
            // Tabel vertikal di mana tahun berada di baris / kolom "Tahun":
            $allAvailableYears = array_values(array_unique($nameYears));
            sort($allAvailableYears);
        } else {
            // Tabel tanpa kolom tahun (snapshot per halaman tabel):
            // Kumpulkan seluruh tahun dari halaman-halaman tabel yang ada pada urusan ini
            // agar pengguna dapat beralih halaman tabel hanya dengan memilih tahun di dropdown filter.
            $tableYears = [];
            foreach ($tables as $tb) {
                if (!empty($tb['tahun'])) {
                    $tableYears[] = (int) $tb['tahun'];
                }
            }
            if (empty($tableYears)) {
                $tableYears = array_merge($rowYears, $nameYears);
            }
            $tableYears = array_values(array_unique($tableYears));
            sort($tableYears);
            $allAvailableYears = !empty($tableYears) ? $tableYears : [$activeTableYear ?: (int) date('Y')];
        }

        // Determine tahun_awal & tahun_akhir within available years
        $minAvail = count($allAvailableYears) > 0 ? min($allAvailableYears) : 2020;
        $maxAvail = count($allAvailableYears) > 0 ? max($allAvailableYears) : 2026;

        $reqAwal = $request->get('tahun_awal');
        $reqAkhir = $request->get('tahun_akhir');
        $sessAwal = session('selected_tahun_awal');

        // Jika ada filter eksplisit di query string (pengguna memilih sendiri di filter rentang tahun):
        if ($reqAkhir !== null && in_array((int)$reqAkhir, $allAvailableYears, true)) {
            $tahunAkhir = (int) $reqAkhir;
        } else {
            // Ketika baru membuka indikator atau pindah-pindah indikator, otomatis ke tahun akhir terbaru (misal: 2027)
            $tahunAkhir = $maxAvail;
        }

        if ($reqAwal !== null && in_array((int)$reqAwal, $allAvailableYears, true)) {
            $tahunAwal = (int) $reqAwal;
        } elseif ($sessAwal !== null && in_array((int)$sessAwal, $allAvailableYears, true) && (int)$sessAwal <= $tahunAkhir) {
            $tahunAwal = (int) $sessAwal;
        } else {
            $tahunAwal = $minAvail;
        }

        if ($tahunAkhir < $tahunAwal) {
            $tahunAwal = $minAvail;
            if ($tahunAkhir < $tahunAwal) {
                $tahunAkhir = $tahunAwal;
            }
        }

        session([
            'selected_tahun_awal' => $tahunAwal,
            'selected_tahun_akhir' => $tahunAkhir,
        ]);

        // Filter columns for table display: keep all non-year columns; for year columns, keep only those within [tahunAwal, tahunAkhir]
        $filterCols = function($cols) use (&$filterCols, $tahunAwal, $tahunAkhir) {
            $res = [];
            foreach ($cols as $col) {
                $cName = trim($col['name'] ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $cName, $m)) {
                    $colYear = (int) $m[0];
                    if ($colYear >= $tahunAwal && $colYear <= $tahunAkhir) {
                        if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                            $col['sub_columns'] = $filterCols($col['sub_columns']);
                        }
                        $res[] = $col;
                    }
                } else {
                    if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                        $col['sub_columns'] = $filterCols($col['sub_columns']);
                    }
                    $res[] = $col;
                }
            }
            return $res;
        };
        $activeColumns = $filterCols($allColumns);

        if ($request->has('tahun') && is_numeric($request->get('tahun'))) {
            $selectedTahunNomor = (int) $request->get('tahun');
        } elseif (!$hasYearColumns && !$hasYearRows && $activeTableYear) {
            $selectedTahunNomor = $activeTableYear;
        } else {
            $selectedTahunNomor = (int) session('selected_tahun', $tahunAkhir);
        }

        if (!empty($allAvailableYears) && !in_array($selectedTahunNomor, $allAvailableYears, true)) {
            $selectedTahunNomor = in_array($activeTableYear, $allAvailableYears, true) ? $activeTableYear : end($allAvailableYears);
        }

        $tahun = Tahun::where('tahun', $selectedTahunNomor)->first()
            ?: Tahun::firstOrCreate(['tahun' => (int) $selectedTahunNomor], ['status_aktif' => true, 'is_default' => false]);

        if ($tahun) {
            session(['selected_tahun' => $tahun->tahun]);
            $selectedTahunId = $tahun->id;
        } else {
            $selectedTahunId = null;
        }

        $search = $request->get('search');

        // Hitung jumlah baris per tabel untuk label tab
        foreach ($tables as &$tbl) {
            $tblId = (int) ($tbl['id'] ?? 1);
            $tbl['row_count'] = Indikator::where('urusan_id', $urusan->id)
                ->where(function ($q) use ($tblId) {
                    if ($tblId === 1) {
                        $q->where('table_id', 1)->orWhereNull('table_id');
                    } else {
                        $q->where('table_id', $tblId);
                    }
                })
                ->whereNull('parent_id')
                ->count();
        }
        unset($tbl);

        // Load root indicators with their nested children recursively for the active table
        $indicatorsQuery = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->whereNull('parent_id')
            ->orderBy('urutan');

        $rootIndikators = $indicatorsQuery
            ->with(['allChildren', 'dataIndikators.tahun'])
            ->get();

        // Flatten indicators into a hierarchical list for convenient display
        $flattenedIndicators = [];
        $buildTree = function ($items, $level = 0, $parentNo = null) use (&$buildTree, &$flattenedIndicators, $selectedTahunId, $activeTableId) {
            foreach ($items as $idx => $item) {
                // Ensure data for selected year is eagerly accessible, fallback to first available if not found
                $dataTahun = $selectedTahunId ? $item->dataIndikators->firstWhere('tahun_id', $selectedTahunId) : null;
                if (!$dataTahun && $item->dataIndikators->count() > 0) {
                    $dataTahun = $item->dataIndikators->first();
                }

                // Jika baris pada halaman tabel ini belum memiliki isi data, otomatis isi dari tahun/tabel sebelumnya
                if (!$dataTahun && $selectedTahunId) {
                    $prevInd = Indikator::where('urusan_id', $item->urusan_id)
                        ->where('id', '!=', $item->id)
                        ->where(function ($q) use ($activeTableId) {
                            $q->where('table_id', '!=', $activeTableId)
                              ->orWhereNull('table_id');
                        })
                        ->where(function ($q) use ($item) {
                            if (!empty(trim($item->nama_indikator))) {
                                $q->where('nama_indikator', $item->nama_indikator);
                            } else {
                                $q->where('urutan', $item->urutan);
                            }
                        })
                        ->first();

                    if ($prevInd) {
                        $prevData = $prevInd->dataIndikators()->latest()->first();
                        if ($prevData) {
                            $dataTahun = new DataIndikator([
                                'indikator_id' => $item->id,
                                'tahun_id' => $selectedTahunId,
                                'nilai' => $prevData->nilai,
                                'sumber_data' => $prevData->sumber_data,
                                'keterangan' => $prevData->keterangan,
                                'custom_fields' => $prevData->custom_fields,
                                'updated_by' => $prevData->updated_by,
                            ]);
                        }
                    }
                }

                $item->current_data = $dataTahun;
                $item->tree_level = $level;
                $itemUrutan = $item->urutan ?: ($idx + 1);
                $currNo = $parentNo ? ($parentNo . '.' . $itemUrutan) : (string)$itemUrutan;
                $item->hierarchical_no = $currNo;
                $flattenedIndicators[] = $item;

                if ($item->children && $item->children->count() > 0) {
                    $buildTree($item->children, $level + 1, $currNo);
                }
            }
        };

        // We load children with their dataIndikators as well
        $loadChildrenRecursively = function ($items) use (&$loadChildrenRecursively) {
            foreach ($items as $item) {
                $item->load(['children' => function ($q) {
                    $q->orderBy('urutan')->with(['dataIndikators.tahun']);
                }]);
                if ($item->children && $item->children->count() > 0) {
                    $loadChildrenRecursively($item->children);
                }
            }
        };

        $loadChildrenRecursively($rootIndikators);
        $buildTree($rootIndikators);

        if ($hasYearRows) {
            $flattenedIndicators = array_values(array_filter($flattenedIndicators, function ($ind) use ($tahunAwal, $tahunAkhir) {
                $name = trim($ind->nama_indikator ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $name, $m)) {
                    $y = (int) $m[0];
                    return $y >= $tahunAwal && $y <= $tahunAkhir;
                }
                return true;
            }));
        }

        // Stats for this active table
        $totalIndikator = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->where('status_aktif', true)
            ->count();

        $totalTerisi = 0;
        $tableLeafIndikators = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->where('status_aktif', true)
            ->doesntHave('children')
            ->with(['urusan', 'dataIndikators.tahun'])
            ->get();

        if ($totalIndikator > 0 && !empty($selectedTahunNomor)) {
            $allDiForUrusan = $tableLeafIndikators->flatMap->dataIndikators;
            foreach ($tableLeafIndikators as $tLeaf) {
                if (\App\Services\DataKelengkapanEvaluator::isFilledForYear($tLeaf, (int) $selectedTahunNomor, $allDiForUrusan)) {
                    $totalTerisi++;
                }
            }
        }

        $allIndikatorsForDropdown = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->where('status_aktif', true)
            ->orderBy('nama_indikator')
            ->get();

        $showFooterTotal = (bool) ($activeTable['show_footer_total'] ?? true);

        $siblingUrusans = Urusan::where('kelompok_urusan_id', $urusan->kelompok_urusan_id)
            ->where('status_aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama_urusan')
            ->get();

        return view('urusan.show', compact(
            'urusan',
            'tahun',
            'selectedTahunId',
            'tables',
            'activeTableId',
            'activeTable',
            'allColumns',
            'activeColumns',
            'tahunAwal',
            'tahunAkhir',
            'allAvailableYears',
            'tableYearMap',
            'rootIndikators',
            'flattenedIndicators',
            'totalIndikator',
            'totalTerisi',
            'search',
            'allIndikatorsForDropdown',
            'showFooterTotal',
            'hasYearColumns',
            'hasYearRows',
            'selectedTahunNomor',
            'siblingUrusans'
        ));
    }

    public function create()
    {
        $kelompoks = KelompokUrusan::orderBy('urutan')->get();
        return view('urusan.create', compact('kelompoks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelompok_urusan_id' => 'required|exists:kelompok_urusan,id',
            'nomor' => 'nullable|integer',
            'kode_urusan' => 'nullable|string|max:50',
            'nama_urusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'urutan' => 'nullable|integer',
        ]);

        // Anti-spam / duplicate prevention: if identical urusan was created in this kelompok within last 5 seconds, reuse it
        $existingRecent = Urusan::where('kelompok_urusan_id', $validated['kelompok_urusan_id'])
            ->where('nama_urusan', $validated['nama_urusan'])
            ->where('created_at', '>=', now()->subSeconds(5))
            ->first();

        if ($existingRecent) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Urusan "' . $existingRecent->nama_urusan . '" berhasil ditambahkan.',
                    'urusan' => [
                        'id' => $existingRecent->id,
                        'kelompok_urusan_id' => $existingRecent->kelompok_urusan_id,
                        'nama_urusan' => $existingRecent->nama_urusan,
                        'formatted_nama' => $existingRecent->formatted_nama,
                        'slug' => $existingRecent->slug ?: $existingRecent->id,
                        'url' => route('urusan.show', $existingRecent->slug ?: $existingRecent->id),
                    ],
                ]);
            }

            return redirect()->route('urusan.show', $existingRecent->slug ?: $existingRecent->id)
                ->with('success', 'Urusan "' . $existingRecent->nama_urusan . '" berhasil ditambahkan.');
        }

        $slugBase = ($request->nomor ? $request->nomor . '-' : '') . $request->nama_urusan;
        $slug = Str::slug($slugBase);
        $originalSlug = $slug;
        $count = 1;
        while (Urusan::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $validated['slug'] = $slug;
        $validated['icon'] = ($validated['icon'] ?? null) ?: 'bi-folder2';
        $validated['urutan'] = ($validated['urutan'] ?? null) ?: ($request->nomor ?: 99);
        $validated['status_aktif'] = true;

        $urusan = Urusan::create($validated);

        ActivityLog::record('create_urusan', 'Menambahkan urusan baru: ' . $urusan->nama_urusan, $urusan);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urusan "' . $urusan->nama_urusan . '" berhasil ditambahkan.',
                'urusan' => [
                    'id' => $urusan->id,
                    'kelompok_urusan_id' => $urusan->kelompok_urusan_id,
                    'nama_urusan' => $urusan->nama_urusan,
                    'formatted_nama' => $urusan->formatted_nama,
                    'slug' => $urusan->slug ?: $urusan->id,
                    'url' => route('urusan.show', $urusan->slug ?: $urusan->id),
                ],
            ]);
        }

        return redirect()->route('urusan.show', $urusan->slug ?: $urusan->id)
            ->with('success', 'Urusan "' . $urusan->nama_urusan . '" berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $urusan = Urusan::findOrFail($id);
        $kelompoks = KelompokUrusan::orderBy('urutan')->get();
        return view('urusan.edit', compact('urusan', 'kelompoks'));
    }

    public function update(Request $request, $id)
    {
        $urusan = Urusan::findOrFail($id);

        $validated = $request->validate([
            'kelompok_urusan_id' => 'required|exists:kelompok_urusan,id',
            'nomor' => 'nullable|integer',
            'kode_urusan' => 'nullable|string|max:50',
            'nama_urusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'urutan' => 'nullable|integer',
            'status_aktif' => 'nullable|boolean',
        ]);

        if ($request->has('status_aktif')) {
            $validated['status_aktif'] = (bool) $validated['status_aktif'];
        } else {
            unset($validated['status_aktif']);
        }
        $urusan->update($validated);

        ActivityLog::record('update_urusan', 'Memperbarui urusan: ' . $urusan->nama_urusan, $urusan);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urusan "' . $urusan->nama_urusan . '" berhasil diperbarui.',
                'urusan' => [
                    'id' => $urusan->id,
                    'kelompok_urusan_id' => $urusan->kelompok_urusan_id,
                    'nama_urusan' => $urusan->nama_urusan,
                    'formatted_nama' => $urusan->formatted_nama,
                    'slug' => $urusan->slug ?: $urusan->id,
                    'url' => route('urusan.show', $urusan->slug ?: $urusan->id),
                ],
            ]);
        }

        return redirect()->route('urusan.show', $urusan->id)
            ->with('success', 'Urusan "' . $urusan->nama_urusan . '" berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $urusan = Urusan::findOrFail($id);
        $nama = $urusan->nama_urusan;
        $slug = $urusan->slug;
        $urusanId = $urusan->id;
        $indikatorCount = $urusan->indikators()->count();

        DB::transaction(function () use ($urusan, $urusanId, $nama, $indikatorCount) {
            $indikatorIds = DB::table('indikator')
                ->where('urusan_id', $urusanId)
                ->select('id');

            DB::table('data_indikator')
                ->whereIn('indikator_id', $indikatorIds)
                ->delete();

            DB::table('indikator')
                ->where('urusan_id', $urusanId)
                ->update(['parent_id' => null]);

            DB::table('indikator')
                ->where('urusan_id', $urusanId)
                ->delete();

            $urusan->delete();
            ActivityLog::record(
                'delete_urusan',
                'Menghapus urusan: ' . $nama . ' beserta ' . $indikatorCount . ' indikator dan data terkait.'
            );
        });

        $successMessage = 'Urusan "' . $nama . '" beserta indikator dan data tahunannya berhasil dihapus.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $successMessage]);
        }

        $refererPath = rtrim((string) parse_url($request->headers->get('referer', ''), PHP_URL_PATH), '/');
        $isViewingDeletedUrusan = preg_match('#/urusan/([^/]+)$#', $refererPath, $matches)
            && in_array(rawurldecode($matches[1]), [(string) $urusanId, $slug], true);

        if ($isViewingDeletedUrusan) {
            return redirect()->route('dashboard')
                ->with('success', $successMessage);
        }

        return back()->with('success', $successMessage);
    }

    public function exportPdf(Request $request, $id)
    {
        $urusan = Urusan::with('kelompokUrusan')->where('slug', $id)->first()
            ?? (is_numeric($id) ? Urusan::with('kelompokUrusan')->findOrFail($id) : null);

        if (!$urusan) {
            abort(404);
        }

        $activeTableId = (int) $request->get('tabel', 1);
        $tables = $urusan->tables;
        $activeTable = $tables[0] ?? ['id' => 1, 'name' => 'Tabel 1', 'columns' => []];
        foreach ($tables as $t) {
            if ((int) ($t['id'] ?? 0) === $activeTableId) {
                $activeTable = $t;
                break;
            }
        }
        $allColumns = $activeTable['columns'] ?? [];

        // Kumpulkan tahun yang tersedia dari kolom dan sub-kolom
        $allAvailableYears = [];
        $extractYears = function($cols) use (&$extractYears, &$allAvailableYears) {
            foreach ($cols as $col) {
                $cName = trim($col['name'] ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $cName, $m)) {
                    $allAvailableYears[] = (int) $m[0];
                }
                if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                    $extractYears($col['sub_columns']);
                }
            }
        };
        $extractYears($allColumns);
        $allAvailableYears = array_values(array_unique($allAvailableYears));
        sort($allAvailableYears);

        $activeIndikatorIds = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->pluck('id');

        $nameYears = Indikator::whereIn('id', $activeIndikatorIds)
            ->pluck('nama_indikator')
            ->filter(fn($n) => preg_match('/^(19|20)\d{2}$/', trim($n)))
            ->map(fn($n) => (int)trim($n))
            ->toArray();

        $hasYearColumns = !empty($allAvailableYears);
        $hasYearRows = !$hasYearColumns && !empty($nameYears);

        if ($hasYearRows) {
            $allAvailableYears = array_values(array_unique($nameYears));
            sort($allAvailableYears);
        }

        $minAvail = count($allAvailableYears) > 0 ? min($allAvailableYears) : 2020;
        $maxAvail = count($allAvailableYears) > 0 ? max($allAvailableYears) : 2026;

        $tahunAkhir = $request->has('tahun_akhir') ? (int) $request->get('tahun_akhir') : $maxAvail;
        $tahunAwal = $request->has('tahun_awal') ? (int) $request->get('tahun_awal') : (session('selected_tahun_awal') && in_array((int)session('selected_tahun_awal'), $allAvailableYears) ? (int)session('selected_tahun_awal') : $minAvail);

        if ($tahunAkhir < $tahunAwal) {
            $tahunAwal = $minAvail;
            if ($tahunAkhir < $tahunAwal) {
                $tahunAkhir = $tahunAwal;
            }
        }

        $activeColumns = [];
        foreach ($allColumns as $col) {
            $cName = trim($col['name'] ?? '');
            if (preg_match('/^(19|20)\d{2}$/', $cName, $m)) {
                $colYear = (int) $m[0];
                if ($colYear >= $tahunAwal && $colYear <= $tahunAkhir) {
                    $activeColumns[] = $col;
                }
            } else {
                $activeColumns[] = $col;
            }
        }

        $rootIndikators = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->with(['children.dataIndikators.tahun', 'dataIndikators.tahun'])
            ->get();

        $flattenedIndicators = [];
        $buildTree = function ($items, $level = 0, $parentItem = null) use (&$buildTree, &$flattenedIndicators) {
            foreach ($items as $item) {
                $item->tree_level = $level;
                $item->parentItem = $parentItem;
                $flattenedIndicators[] = $item;
                if ($item->children && $item->children->count() > 0) {
                    $buildTree($item->children, $level + 1, $item);
                }
            }
        };

        $loadChildrenRecursively = function ($items) use (&$loadChildrenRecursively) {
            foreach ($items as $item) {
                $item->load(['children' => function ($q) {
                    $q->orderBy('urutan')->with(['dataIndikators.tahun']);
                }]);
                if ($item->children && $item->children->count() > 0) {
                    $loadChildrenRecursively($item->children);
                }
            }
        };

        $loadChildrenRecursively($rootIndikators);
        $buildTree($rootIndikators);

        if ($hasYearRows) {
            $flattenedIndicators = array_values(array_filter($flattenedIndicators, function ($ind) use ($tahunAwal, $tahunAkhir) {
                $name = trim($ind->nama_indikator ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $name, $m)) {
                    $y = (int) $m[0];
                    return $y >= $tahunAwal && $y <= $tahunAkhir;
                }
                return true;
            }));
        }

        $tahun = Tahun::where('tahun', $tahunAkhir)->first() ?: Tahun::where('is_default', true)->first();

        ActivityLog::record(
            'export_pdf',
            'Mengunduh PDF data sektoral urusan ' . $urusan->formatted_nama . ' tabel ' . ($activeTable['name'] ?? 'Tabel 1')
        );

        $logoPath = public_path('logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $pdf = Pdf::loadView('urusan.pdf', compact('urusan', 'activeTable', 'activeColumns', 'flattenedIndicators', 'tahunAwal', 'tahunAkhir', 'tahun', 'logoBase64'))
            ->setPaper('a4', 'landscape');

        $slugNama = Str::slug($urusan->nama_urusan ?: 'urusan');
        $filename = 'Data_Sektoral_' . ($slugNama ?: 'urusan') . '_' . ($tahunAwal . '-' . $tahunAkhir) . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request, $id, ExcelService $excelService)
    {
        $urusan = Urusan::with('kelompokUrusan')->where('slug', $id)->first()
            ?? (is_numeric($id) ? Urusan::with('kelompokUrusan')->findOrFail($id) : null);

        if (!$urusan) {
            abort(404);
        }

        $activeTableId = (int) $request->get('tabel', 1);
        $tables = $urusan->tables;
        $activeTable = $tables[0] ?? ['id' => 1, 'name' => 'Tabel 1', 'columns' => []];
        foreach ($tables as $t) {
            if ((int) ($t['id'] ?? 0) === $activeTableId) {
                $activeTable = $t;
                break;
            }
        }
        $allColumns = $activeTable['columns'] ?? [];

        $allAvailableYears = [];
        $extractYears = function($cols) use (&$extractYears, &$allAvailableYears) {
            foreach ($cols as $col) {
                $cName = trim($col['name'] ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $cName, $m)) {
                    $allAvailableYears[] = (int) $m[0];
                }
                if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                    $extractYears($col['sub_columns']);
                }
            }
        };
        $extractYears($allColumns);
        $allAvailableYears = array_values(array_unique($allAvailableYears));
        sort($allAvailableYears);

        $activeIndikatorIds = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->pluck('id');

        $nameYears = Indikator::whereIn('id', $activeIndikatorIds)
            ->pluck('nama_indikator')
            ->filter(fn($n) => preg_match('/^(19|20)\d{2}$/', trim($n)))
            ->map(fn($n) => (int)trim($n))
            ->toArray();

        $hasYearColumns = !empty($allAvailableYears);
        $hasYearRows = !$hasYearColumns && !empty($nameYears);

        if ($hasYearRows) {
            $allAvailableYears = array_values(array_unique($nameYears));
            sort($allAvailableYears);
        }

        $minAvail = count($allAvailableYears) > 0 ? min($allAvailableYears) : 2020;
        $maxAvail = count($allAvailableYears) > 0 ? max($allAvailableYears) : 2026;

        $tahunAkhir = $request->has('tahun_akhir') ? (int) $request->get('tahun_akhir') : $maxAvail;
        $tahunAwal = $request->has('tahun_awal') ? (int) $request->get('tahun_awal') : (session('selected_tahun_awal') && in_array((int)session('selected_tahun_awal'), $allAvailableYears) ? (int)session('selected_tahun_awal') : $minAvail);

        if ($tahunAkhir < $tahunAwal) {
            $tahunAwal = $minAvail;
            if ($tahunAkhir < $tahunAwal) {
                $tahunAkhir = $tahunAwal;
            }
        }

        ActivityLog::record(
            'export_excel',
            'Mengunduh Excel data sektoral urusan ' . $urusan->formatted_nama . ' tabel ' . ($activeTable['name'] ?? 'Tabel 1')
        );

        return $excelService->export($urusan, $activeTableId, $tahunAwal, $tahunAkhir, false);
    }

    public function downloadTemplate(Request $request, $id, ExcelService $excelService)
    {
        $urusan = Urusan::with('kelompokUrusan')->where('slug', $id)->first()
            ?? (is_numeric($id) ? Urusan::with('kelompokUrusan')->findOrFail($id) : null);

        if (!$urusan) {
            abort(404);
        }

        $activeTableId = (int) $request->get('tabel', 1);
        $tables = $urusan->tables;
        $activeTable = $tables[0] ?? ['id' => 1, 'name' => 'Tabel 1', 'columns' => []];
        foreach ($tables as $t) {
            if ((int) ($t['id'] ?? 0) === $activeTableId) {
                $activeTable = $t;
                break;
            }
        }
        $allColumns = $activeTable['columns'] ?? [];

        $allAvailableYears = [];
        $extractYears = function($cols) use (&$extractYears, &$allAvailableYears) {
            foreach ($cols as $col) {
                $cName = trim($col['name'] ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $cName, $m)) {
                    $allAvailableYears[] = (int) $m[0];
                }
                if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                    $extractYears($col['sub_columns']);
                }
            }
        };
        $extractYears($allColumns);
        $allAvailableYears = array_values(array_unique($allAvailableYears));
        sort($allAvailableYears);

        $activeIndikatorIds = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->pluck('id');

        $nameYears = Indikator::whereIn('id', $activeIndikatorIds)
            ->pluck('nama_indikator')
            ->filter(fn($n) => preg_match('/^(19|20)\d{2}$/', trim($n)))
            ->map(fn($n) => (int)trim($n))
            ->toArray();

        $hasYearColumns = !empty($allAvailableYears);
        $hasYearRows = !$hasYearColumns && !empty($nameYears);

        if ($hasYearRows) {
            $allAvailableYears = array_values(array_unique($nameYears));
            sort($allAvailableYears);
        }

        $minAvail = count($allAvailableYears) > 0 ? min($allAvailableYears) : 2020;
        $maxAvail = count($allAvailableYears) > 0 ? max($allAvailableYears) : 2026;

        $tahunAkhir = $request->has('tahun_akhir') ? (int) $request->get('tahun_akhir') : $maxAvail;
        $tahunAwal = $request->has('tahun_awal') ? (int) $request->get('tahun_awal') : (session('selected_tahun_awal') && in_array((int)session('selected_tahun_awal'), $allAvailableYears) ? (int)session('selected_tahun_awal') : $minAvail);

        return $excelService->export($urusan, $activeTableId, $tahunAwal, $tahunAkhir, true);
    }

    public function importExcel(Request $request, $id, ExcelService $excelService)
    {
        $urusan = Urusan::where('slug', $id)->first()
            ?? (is_numeric($id) ? Urusan::findOrFail($id) : null);

        if (!$urusan) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'table_id' => 'nullable|integer',
        ], [
            'file.required' => 'Silakan pilih file Excel atau CSV yang akan diunggah.',
            'file.mimes' => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $activeTableId = (int) $request->input('table_id', 1);
        $options = [
            'create_missing' => $request->boolean('create_missing', true),
            'update_existing' => $request->boolean('update_existing', true),
        ];

        try {
            $result = $excelService->import($request->file('file'), $urusan, $activeTableId, $options);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Throwable $e) {
            report($e);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    public function updateColumns(Request $request, $id)
    {
        $urusan = is_numeric($id) ? Urusan::findOrFail($id) : Urusan::where('slug', $id)->firstOrFail();

        $action = $request->input('action', 'add');
        $activeTableId = (int) $request->input('table_id', 1);
        $tables = $urusan->tables;

        if ($action === 'setup_table') {
            $numCols = (int) $request->input('num_cols', 3);
            $numRows = (int) $request->input('num_rows', 5);
            $colNames = $request->input('col_names', []);
            $colTypes = $request->input('col_types', []);
            $selectedTahunNomor = $request->input('tahun');

            $numCols = max(0, min(20, $numCols));
            $numRows = max(0, min(100, $numRows));

            // Jika tahun dipilih/diinput, pastikan ada di database dan sinkronkan ke session
            if ($selectedTahunNomor) {
                $tahunModel = Tahun::firstOrCreate(
                    ['tahun' => (int) $selectedTahunNomor],
                    [
                        'status_aktif' => true,
                        'is_default' => false,
                        'keterangan' => 'Tahun Anggaran ' . (int) $selectedTahunNomor,
                    ]
                );
                session(['selected_tahun' => (int) $tahunModel->tahun]);
            }

            $colIds = $request->input('col_ids', []);
            $newColumns = [];

            // Ambil kolom lama pada tabel ini untuk mempertahankan ID dan data yang sudah terisi
            $oldTableCols = [];
            foreach ($tables as $t) {
                if ((int) ($t['id'] ?? 0) === $activeTableId) {
                    $oldTableCols = $t['columns'] ?? [];
                    break;
                }
            }

            for ($i = 0; $i < $numCols; $i++) {
                $name = isset($colNames[$i]) && trim($colNames[$i]) !== '' ? trim($colNames[$i]) : ('Kolom ' . ($i + 1));
                $type = isset($colTypes[$i]) ? $colTypes[$i] : 'text';

                $lower = strtolower($name);
                if ($lower === 'satuan') {
                    $cId = 'col_satuan';
                } elseif ($lower === 'nilai' || str_starts_with($lower, 'nilai ')) {
                    $cId = 'col_nilai';
                    $type = 'number';
                } elseif ($lower === 'sumber data' || $lower === 'sumber_data') {
                    $cId = 'col_sumber';
                } else {
                    $existingId = !empty($colIds[$i]) ? $colIds[$i] : ($oldTableCols[$i]['id'] ?? null);
                    if ($existingId && !in_array($existingId, ['col_satuan', 'col_nilai', 'col_sumber'])) {
                        $cId = $existingId;
                    } else {
                        $cId = 'col_' . time() . '_' . ($i + 1);
                    }
                }

                $colData = [
                    'id' => $cId,
                    'name' => $name,
                    'type' => $type,
                ];

                // Pertahankan sub-kolom jika kolom ini sebelumnya sudah memiliki sub-kolom
                if (!empty($oldTableCols[$i]['sub_columns']) && ($oldTableCols[$i]['id'] ?? '') === $cId) {
                    $colData['sub_columns'] = $oldTableCols[$i]['sub_columns'];
                }

                $newColumns[] = $colData;
            }

            // Simpan ke konfigurasi tabel terkait
            $showFooterTotal = $request->boolean('show_footer_total');
            $showFooterJumlah = $request->has('show_footer_jumlah') ? $request->boolean('show_footer_jumlah') : true;
            $showFooterSubtotal = $request->has('show_footer_subtotal') ? $request->boolean('show_footer_subtotal') : true;
            $footerMode = $request->input('footer_mode');

            if (!$showFooterTotal) {
                $showFooterJumlah = false;
                $showFooterSubtotal = false;
                $footerMode = 'none';
            } elseif ($footerMode === 'jumlah') {
                $showFooterJumlah = true;
                $showFooterSubtotal = false;
            } elseif ($footerMode === 'total') {
                $showFooterJumlah = false;
                $showFooterSubtotal = true;
            } elseif ($footerMode === 'both') {
                $showFooterJumlah = true;
                $showFooterSubtotal = true;
            }


            $footerLabel = trim($request->input('footer_label', 'Jumlah')) ?: 'Jumlah';
            $footerSubtotalLabel = trim($request->input('footer_subtotal_label', 'TOTAL')) ?: 'TOTAL';

            $tableFound = false;
            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    $tbl['columns'] = $newColumns;
                    $tbl['show_footer_total'] = $showFooterTotal;
                    $tbl['show_footer_jumlah'] = $showFooterJumlah;
                    $tbl['show_footer_subtotal'] = $showFooterSubtotal;
                    $tbl['footer_label'] = $footerLabel;
                    $tbl['footer_subtotal_label'] = $footerSubtotalLabel;
                    if ($footerMode) {
                        $tbl['footer_mode'] = $footerMode;
                    }
                    $tableFound = true;
                    break;
                }
            }
            unset($tbl);

            if (!$tableFound) {
                $tables[] = [
                    'id' => $activeTableId,
                    'name' => 'Tabel ' . $activeTableId,
                    'columns' => $newColumns,
                    'show_footer_total' => $showFooterTotal,
                    'show_footer_jumlah' => $showFooterJumlah,
                    'show_footer_subtotal' => $showFooterSubtotal,
                    'footer_label' => $footerLabel,
                    'footer_subtotal_label' => $footerSubtotalLabel,
                    'footer_mode' => $footerMode ?: ($showFooterTotal ? 'both' : 'none'),
                ];
            }

            $updatePayload = ['table_configs' => array_values($tables)];
            if ($activeTableId === 1) {
                $updatePayload['custom_columns'] = $newColumns;
            }
            $urusan->update($updatePayload);

            // Adjust indicators (rows) khusus untuk tabel ini
            $existingRootCount = Indikator::where('urusan_id', $urusan->id)
                ->where(function ($q) use ($activeTableId) {
                    if ($activeTableId === 1) {
                        $q->where('table_id', 1)->orWhereNull('table_id');
                    } else {
                        $q->where('table_id', $activeTableId);
                    }
                })
                ->whereNull('parent_id')
                ->count();

            if ($numRows > $existingRootCount) {
                $toAdd = $numRows - $existingRootCount;
                $maxUrutan = Indikator::where('urusan_id', $urusan->id)
                    ->where(function ($q) use ($activeTableId) {
                        if ($activeTableId === 1) {
                            $q->where('table_id', 1)->orWhereNull('table_id');
                        } else {
                            $q->where('table_id', $activeTableId);
                        }
                    })
                    ->whereNull('parent_id')
                    ->max('urutan') ?? 0;

                for ($r = 1; $r <= $toAdd; $r++) {
                    $curNum = $existingRootCount + $r;
                    Indikator::create([
                        'urusan_id' => $urusan->id,
                        'table_id' => $activeTableId,
                        'parent_id' => null,
                        'nama_indikator' => '',
                        'tipe_data' => 'numerik',
                        'urutan' => $maxUrutan + $r,
                        'status_aktif' => true,
                    ]);
                }
            }

            $activeYear = $selectedTahunNomor ?: session('selected_tahun', Tahun::getDefaultTahun());

            ActivityLog::record(
                'setup_table',
                "Mengatur tabel {$urusan->nama_urusan} (Tabel #{$activeTableId}): {$numCols} kolom dan {$numRows} baris",
                $urusan
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Tabel berhasil diatur ({$numCols} kolom dan {$numRows} baris).",
                    'redirect_url' => route('urusan.show', [
                        'id' => $urusan->slug ?: $urusan->id,
                        'tabel' => $activeTableId,
                    ]),
                ]);
            }

            return redirect()->route('urusan.show', [
                'id' => $urusan->slug ?: $urusan->id,
                'tabel' => $activeTableId,
            ])->with('success', "Tabel berhasil diatur ({$numCols} kolom dan {$numRows} baris).");
        }

        if ($action === 'rename_table') {
            $tableIdToRename = (int) $request->input('table_id', $activeTableId);
            $newName = trim($request->input('nama_tabel') ?? $request->input('name') ?? '');
            $newTahun = $request->input('tahun');
            if ($newName) {
                foreach ($tables as &$tbl) {
                    if ((int) ($tbl['id'] ?? 0) === $tableIdToRename) {
                        $tbl['name'] = $newName;
                        if ($newTahun) {
                            $tbl['tahun_range'] = trim($newTahun);
                            if (preg_match_all('/(19|20)\d{2}/', $newTahun, $matches) && !empty($matches[0])) {
                                $tbl['tahun'] = (int) end($matches[0]);
                            } elseif (is_numeric($newTahun)) {
                                $tbl['tahun'] = (int) $newTahun;
                            }
                        } elseif (preg_match('/(19|20)\d{2}/', $newName, $mYr)) {
                            $tbl['tahun'] = (int) $mYr[0];
                            $tbl['tahun_range'] = (string) $mYr[0];
                        }
                        break;
                    }
                }
                unset($tbl);
                $urusan->update(['table_configs' => array_values($tables)]);

                ActivityLog::record(
                    'rename_table',
                    "Mengubah nama halaman tabel #{$tableIdToRename} menjadi '{$newName}' pada urusan {$urusan->nama_urusan}",
                    $urusan
                );

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Nama halaman berhasil diubah menjadi '{$newName}'.",
                    ]);
                }

                return redirect()->route('urusan.show', [
                    'id' => $urusan->slug ?: $urusan->id,
                    'tabel' => $tableIdToRename,
                ])->with('success', "Nama halaman berhasil diubah menjadi '{$newName}'.");
            }
        }

        if ($action === 'clear') {
            $selectedTahunNomor = $request->input('tahun');
            if ($selectedTahunNomor) {
                session(['selected_tahun' => (int) $selectedTahunNomor]);
            }

            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    $tbl['columns'] = [];
                    break;
                }
            }
            unset($tbl);

            $updatePayload = ['table_configs' => array_values($tables)];
            if ($activeTableId === 1) {
                $updatePayload['custom_columns'] = [];
            }
            $urusan->update($updatePayload);

            ActivityLog::record(
                'clear_columns',
                "Mengosongkan kolom tabel #{$activeTableId} pada urusan {$urusan->nama_urusan}",
                $urusan
            );

            $activeYear = $selectedTahunNomor ?: session('selected_tahun', Tahun::getDefaultTahun());

            return redirect()->route('urusan.show', [
                'id' => $urusan->slug ?: $urusan->id,
                'tabel' => $activeTableId,
                'tahun' => $activeYear,
            ])->with('success', 'Seluruh kolom pada tabel ini berhasil dikosongkan.');
        }

        if ($action === 'add' || $action === 'add_column') {
            $colName = trim($request->input('name', ''));
            if ($colName === '') {
                $colName = 'Kolom Baru';
            }
            $colType = $request->input('type', 'text');
            if (!in_array($colType, ['text', 'number', 'percentage'])) {
                $colType = 'text';
            }

            $lower = strtolower($colName);
            if ($lower === 'satuan') {
                $cId = 'col_satuan';
            } elseif ($lower === 'nilai' || str_starts_with($lower, 'nilai ')) {
                $cId = 'col_nilai';
                $colType = 'number';
            } elseif ($lower === 'sumber data' || $lower === 'sumber_data') {
                $cId = 'col_sumber';
            } else {
                $cId = 'col_' . time() . '_' . rand(100, 999);
            }

            // Dapatkan kolom yang sudah ada pada tabel aktif
            $existingCols = [];
            foreach ($tables as $t) {
                if ((int) ($t['id'] ?? 0) === $activeTableId) {
                    $existingCols = $t['columns'] ?? [];
                    break;
                }
            }

            // Validasi: Cegah penambahan kolom dengan nama yang sama (duplikat) pada tabel aktif
            foreach ($existingCols as $ec) {
                if (strcasecmp(trim($ec['name'] ?? ''), $colName) === 0) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Kolom dengan nama '{$colName}' sudah ada pada tabel ini. Tidak dapat menambahkan kolom duplikat.",
                        ], 422);
                    }
                    return back()->with('error', "Kolom dengan nama '{$colName}' sudah ada pada tabel ini. Tidak dapat menambahkan kolom duplikat.");
                }
            }

            $newColData = [
                'id' => $cId,
                'name' => $colName,
                'type' => $colType,
            ];

            // Cek apakah kolom baru merupakan Tahun (misal: 2027)
            $isNewYear = preg_match('/^(19|20)\d{2}$/', $colName, $mNewYear);
            $newYearNum = $isNewYear ? (int) $mNewYear[0] : null;

            // Kumpulkan semua kolom tahun yang sudah ada
            $existingYearCols = [];
            foreach ($existingCols as $ec) {
                $ecName = trim($ec['name'] ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $ecName, $mEC)) {
                    $yr = (int) $mEC[0];
                    $existingYearCols[$yr] = $ec;
                }
            }
            ksort($existingYearCols);

            $prevYearCol = null;
            $prevYearNum = null;

            if ($isNewYear) {
                // Cari tahun tertinggi yang lebih kecil dari tahun baru
                foreach (array_reverse($existingYearCols, true) as $yr => $ec) {
                    if ($yr < $newYearNum) {
                        $prevYearCol = $ec;
                        $prevYearNum = $yr;
                        break;
                    }
                }
                // Jika tidak ada yang lebih kecil, ambil tahun terakhir yang ada
                if (!$prevYearCol && count($existingYearCols) > 0) {
                    $prevYearNum = array_key_last($existingYearCols);
                    $prevYearCol = $existingYearCols[$prevYearNum];
                }
            } else {
                // Jika bukan nama tahun, cek apakah kolom tahun terakhir memiliki sub-kolom
                if (count($existingYearCols) > 0) {
                    $lastYr = array_key_last($existingYearCols);
                    if (!empty($existingYearCols[$lastYr]['sub_columns'])) {
                        $prevYearCol = $existingYearCols[$lastYr];
                        $prevYearNum = $lastYr;
                    }
                }
            }

            $subColMapping = []; // [old_sub_id => new_sub_id]

            // 1. OTOMATIS CLONE SUB-KOLOM DARI TAHUN SEBELUMNYA
            if ($prevYearCol && !empty($prevYearCol['sub_columns'])) {
                $clonedSubs = [];
                foreach ($prevYearCol['sub_columns'] as $idx => $oldSub) {
                    $newSubId = $cId . '_sub_' . ($idx + 1);
                    $clonedSubs[] = [
                        'id' => $newSubId,
                        'name' => $oldSub['name'],
                        'type' => $oldSub['type'] ?? 'number',
                        'is_total' => $oldSub['is_total'] ?? false,
                    ];
                    $subColMapping[$oldSub['id']] = $newSubId;
                }
                $newColData['sub_columns'] = $clonedSubs;
                $newColData['type'] = 'number';
            }

            $tableFound = false;
            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    $cols = $tbl['columns'] ?? [];
                    $cols[] = $newColData;
                    $tbl['columns'] = $cols;
                    $tableFound = true;
                    break;
                }
            }
            unset($tbl);

            if (!$tableFound) {
                $tables[] = [
                    'id' => $activeTableId,
                    'name' => 'Tabel ' . $activeTableId,
                    'columns' => [$newColData],
                ];
            }

            $updatePayload = ['table_configs' => array_values($tables)];
            if ($activeTableId === 1) {
                $activeTableCols = [];
                foreach ($tables as $t) {
                    if ((int) ($t['id'] ?? 0) === 1) {
                        $activeTableCols = $t['columns'];
                        break;
                    }
                }
                $updatePayload['custom_columns'] = $activeTableCols;
            }
            $urusan->update($updatePayload);

            // 2. OTOMATIS COPY / PREFILL DATA DARI TAHUN SEBELUMNYA
            if ($isNewYear && $prevYearNum && $newYearNum) {
                $newTahunModel = Tahun::autoRegisterYear($newYearNum);
                $prevTahunModel = Tahun::where('tahun', $prevYearNum)->first();

                if ($prevTahunModel) {
                    $indicators = Indikator::where('urusan_id', $urusan->id)
                        ->where(function ($q) use ($activeTableId) {
                            if ($activeTableId === 1) {
                                $q->where('table_id', 1)->orWhereNull('table_id');
                            } else {
                                $q->where('table_id', $activeTableId);
                            }
                        })
                        ->get();

                    foreach ($indicators as $ind) {
                        $prevData = DataIndikator::where('indikator_id', $ind->id)
                            ->where('tahun_id', $prevTahunModel->id)
                            ->first();

                        if ($prevData) {
                            $newData = DataIndikator::firstOrNew([
                                'indikator_id' => $ind->id,
                                'tahun_id' => $newTahunModel->id,
                            ]);

                            $cFields = $newData->custom_fields ?? [];

                            // Pertahankan metadata standar
                            foreach (['col_1792135785_1', 'col_1792135785_2', 'col_1792492220_1', 'col_1792492220_2', 'col_1792493502_1', 'col_1792493502_2', 'col_siswa_1', 'col_siswa_2', 'col_satuan'] as $metaK) {
                                if ($prevData->getCustomValue($metaK)) {
                                    $cFields[$metaK] = $prevData->getCustomValue($metaK);
                                }
                            }

                            // Salin nilai sub-kolom
                            if (!empty($subColMapping)) {
                                foreach ($subColMapping as $oldSubId => $newSubId) {
                                    $val = $prevData->getCustomValue($oldSubId);
                                    if ($val !== null) {
                                        $cFields[$newSubId] = (string) $val;
                                    }
                                }
                            } elseif (isset($prevYearCol['id'])) {
                                $val = $prevData->getCustomValue($prevYearCol['id']) ?? $prevData->nilai;
                                if ($val !== null) {
                                    $cFields[$cId] = (string) $val;
                                }
                            }

                            $newData->custom_fields = $cFields;
                            $newData->nilai = $prevData->nilai;
                            $newData->save();
                        }
                    }
                }
            }

            ActivityLog::record(
                'add_column',
                "Menambahkan kolom '{$colName}' pada tabel #{$activeTableId} urusan {$urusan->nama_urusan}",
                $urusan
            );

            $redirectParams = [
                'id' => $urusan->slug ?: $urusan->id,
                'tabel' => $activeTableId,
            ];

            if ($isNewYear) {
                session(['selected_tahun' => $newYearNum]);

                $curAwal = (int) session('selected_tahun_awal', 0);
                $curAkhir = (int) session('selected_tahun_akhir', 0);

                if ($curAkhir === 0 || $newYearNum > $curAkhir) {
                    session(['selected_tahun_akhir' => $newYearNum]);
                }
                if ($curAwal === 0 || $newYearNum < $curAwal) {
                    session(['selected_tahun_awal' => $newYearNum]);
                }

                $redirectParams['tahun_awal'] = session('selected_tahun_awal');
                $redirectParams['tahun_akhir'] = session('selected_tahun_akhir');
            } else {
                $redirectParams['tahun'] = session('selected_tahun', Tahun::getDefaultTahun());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Kolom '{$colName}' berhasil ditambahkan.",
                    'column' => $newColData,
                    'new_year' => $newYearNum,
                    'redirect_url' => route('urusan.show', $redirectParams),
                ]);
            }

            return redirect()->route('urusan.show', $redirectParams)
                ->with('success', "Kolom '{$colName}' berhasil ditambahkan ke tabel beserta struktur sub-kolom dan data tahun sebelumnya.");
        }

        if ($action === 'edit' || $action === 'edit_column') {
            $columnId = $request->input('column_id');
            $colName = trim($request->input('name', ''));
            $colType = $request->input('type', 'text');

            if ($columnId && $colName !== '') {
                // 1. Validasi: Cegah duplikasi nama kolom pada tabel yang sama
                foreach ($tables as $tblCheck) {
                    if ((int) ($tblCheck['id'] ?? 0) === $activeTableId) {
                        foreach ($tblCheck['columns'] ?? [] as $cCheck) {
                            if (($cCheck['id'] ?? '') !== $columnId && strcasecmp(trim($cCheck['name'] ?? ''), $colName) === 0) {
                                if ($request->ajax() || $request->wantsJson()) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Nama kolom '{$colName}' sudah ada pada tabel ini. Harap gunakan nama kolom yang berbeda agar tidak terjadi duplikasi.",
                                    ], 422);
                                }
                                return back()->with('error', "Nama kolom '{$colName}' sudah ada pada tabel ini. Harap gunakan nama kolom yang berbeda agar tidak terjadi duplikasi.");
                            }
                        }
                        break;
                    }
                }

                $oldColName = '';
                foreach ($tables as &$tbl) {
                    if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                        $cols = $tbl['columns'] ?? [];
                        $editInTree = function(&$items) use (&$editInTree, $columnId, $colName, $colType, &$oldColName) {
                            foreach ($items as &$col) {
                                if (($col['id'] ?? '') === $columnId) {
                                    $oldColName = trim($col['name'] ?? '');
                                    $col['name'] = $colName;
                                    if ($colType) {
                                        $col['type'] = $colType;
                                    }
                                    return true;
                                }
                                if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                                    if ($editInTree($col['sub_columns'])) {
                                        return true;
                                    }
                                }
                            }
                            unset($col);
                            return false;
                        };
                        $editInTree($cols);
                        $tbl['columns'] = $cols;
                        break;
                    }
                }
                unset($tbl);

                $updatePayload = ['table_configs' => array_values($tables)];
                if ($activeTableId === 1) {
                    foreach ($tables as $t) {
                        if ((int) ($t['id'] ?? 0) === 1) {
                            $updatePayload['custom_columns'] = $t['columns'];
                            break;
                        }
                    }
                }
                $urusan->update($updatePayload);

                // Migrasi tahun jika kolom yang di-rename adalah kolom tahun
                $isOldYear = preg_match('/^(19|20)\d{2}$/', $oldColName, $mOldYear);
                $isNewYear = preg_match('/^(19|20)\d{2}$/', $colName, $mNewYear);

                if ($isNewYear) {
                    $newYearNum = (int) $mNewYear[0];
                    $newTahunModel = Tahun::firstOrCreate(
                        ['tahun' => $newYearNum],
                        [
                            'status_aktif' => true,
                            'is_default' => false,
                            'keterangan' => 'Tahun Anggaran ' . $newYearNum,
                        ]
                    );

                    if ($isOldYear) {
                        $oldYearNum = (int) $mOldYear[0];
                        if ($oldYearNum !== $newYearNum) {
                            $oldTahunModel = Tahun::where('tahun', $oldYearNum)->first();
                            if ($oldTahunModel) {
                                $indicators = Indikator::where('urusan_id', $urusan->id)
                                    ->where(function ($q) use ($activeTableId) {
                                        if ($activeTableId === 1) {
                                            $q->where('table_id', 1)->orWhereNull('table_id');
                                        } else {
                                            $q->where('table_id', $activeTableId);
                                        }
                                    })
                                    ->get();

                                foreach ($indicators as $ind) {
                                    $oldData = DataIndikator::where('indikator_id', $ind->id)
                                        ->where('tahun_id', $oldTahunModel->id)
                                        ->first();
                                    if ($oldData) {
                                        $newData = DataIndikator::firstOrNew([
                                            'indikator_id' => $ind->id,
                                            'tahun_id' => $newTahunModel->id,
                                        ]);
                                        if (empty($newData->custom_fields)) {
                                            $newData->custom_fields = $oldData->custom_fields;
                                            $newData->nilai = $oldData->nilai;
                                            $newData->updated_by = auth()->id() ?? 1;
                                            $newData->save();
                                        }
                                    }
                                }
                            }
                        }
                    }

                    session(['selected_tahun' => $newYearNum]);
                }

                ActivityLog::record(
                    'edit_column',
                    "Mengubah kolom '{$colName}' pada tabel #{$activeTableId} urusan {$urusan->nama_urusan}",
                    $urusan
                );

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Kolom '{$colName}' berhasil diperbarui.",
                        'redirect_url' => route('urusan.show', [
                            'id' => $urusan->slug ?: $urusan->id,
                            'tabel' => $activeTableId,
                            'tahun' => session('selected_tahun', Tahun::getDefaultTahun()),
                        ]),
                    ]);
                }

                return redirect()->route('urusan.show', [
                    'id' => $urusan->slug ?: $urusan->id,
                    'tabel' => $activeTableId,
                    'tahun' => session('selected_tahun', Tahun::getDefaultTahun()),
                ])->with('success', "Kolom '{$colName}' berhasil diperbarui.");
            }
        }

        if ($action === 'delete' || $action === 'delete_column') {
            $columnId = $request->input('column_id');

            if ($columnId) {
                $deletedName = '';
                foreach ($tables as &$tbl) {
                    if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                        $cols = $tbl['columns'] ?? [];
                        $filteredCols = [];
                        foreach ($cols as $col) {
                            if (($col['id'] ?? '') === $columnId) {
                                $deletedName = $col['name'] ?? 'Kolom';
                            } else {
                                $filteredCols[] = $col;
                            }
                        }
                if ($deletedName === '') {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kolom tidak ditemukan pada tabel ini.',
                        ], 404);
                    }
                    return back()->with('error', 'Kolom tidak ditemukan pada tabel ini.');
                }

                $tbl['columns'] = array_values($filteredCols);
                break;
            }
        }
        unset($tbl);

        $updatePayload = ['table_configs' => array_values($tables)];
        if ($activeTableId === 1) {
            foreach ($tables as $t) {
                if ((int) ($t['id'] ?? 0) === 1) {
                    $updatePayload['custom_columns'] = $t['columns'];
                    break;
                }
            }
        }
        $urusan->update($updatePayload);

        // Jika kolom yang dihapus merupakan kolom tahun (misal: 2027)
        if (preg_match('/^(19|20)\d{2}$/', trim($deletedName), $mDelYear)) {
            $delYearNum = (int) $mDelYear[0];
            if ((int) session('selected_tahun') === $delYearNum) {
                session()->forget('selected_tahun');
            }
            if ((int) session('selected_tahun_awal') === $delYearNum) {
                session()->forget('selected_tahun_awal');
            }
            if ((int) session('selected_tahun_akhir') === $delYearNum) {
                session()->forget('selected_tahun_akhir');
            }

            $delTahunModel = Tahun::where('tahun', $delYearNum)->first();
            if ($delTahunModel) {
                $indIds = Indikator::where('urusan_id', $urusan->id)
                    ->where(function ($q) use ($activeTableId) {
                        if ($activeTableId === 1) {
                            $q->where('table_id', 1)->orWhereNull('table_id');
                        } else {
                            $q->where('table_id', $activeTableId);
                        }
                    })->pluck('id');

                DataIndikator::whereIn('indikator_id', $indIds)
                    ->where('tahun_id', $delTahunModel->id)
                    ->delete();

                // Bersihkan tahun jika tidak memiliki data_indikator lagi dan tidak digunakan di tabel manapun
                $remainingData = DataIndikator::where('tahun_id', $delTahunModel->id)->exists();
                if (!$remainingData && !$delTahunModel->is_default) {
                    $isStillReferenced = Urusan::all()->contains(function ($u) use ($delYearNum) {
                        $configs = is_array($u->table_configs) ? $u->table_configs : [];
                        foreach ($configs as $tbl) {
                            foreach ($tbl['columns'] ?? [] as $col) {
                                if (isset($col['name']) && (string) $col['name'] === (string) $delYearNum) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    });

                    if (!$isStillReferenced) {
                        $delTahunModel->delete();
                    }
                }
            }
        }

        ActivityLog::record(
            'delete_column',
            "Menghapus kolom '{$deletedName}' pada tabel #{$activeTableId} urusan {$urusan->nama_urusan}",
            $urusan
        );

        $redirectUrl = route('urusan.show', [
            'id' => $urusan->slug ?: $urusan->id,
            'tabel' => $activeTableId,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kolom '{$deletedName}' berhasil dihapus.",
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl)->with('success', "Kolom '{$deletedName}' berhasil dihapus dari tabel.");
            }
        }

        if ($action === 'set_sub_columns' || $action === 'save_sub_columns') {
            $parentColId = $request->input('parent_col_id');
            $subNames = $request->input('sub_names');
            $applyToAllYears = $request->boolean('apply_to_all_years');

            if (is_string($subNames)) {
                $subNames = array_map('trim', explode(',', $subNames));
            }
            if (!is_array($subNames)) {
                $subNames = [];
            }
            $subNames = array_values(array_filter($subNames, fn($n) => trim($n) !== ''));

            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    $cols = $tbl['columns'] ?? [];

                    // Recursive finder for target parent column
                    $findInTree = function($items, $targetId) use (&$findInTree) {
                        foreach ($items as $item) {
                            if (($item['id'] ?? '') === $targetId) {
                                return $item;
                            }
                            if (!empty($item['sub_columns']) && is_array($item['sub_columns'])) {
                                $found = $findInTree($item['sub_columns'], $targetId);
                                if ($found) return $found;
                            }
                        }
                        return null;
                    };

                    $parentCol = $findInTree($cols, $parentColId);
                    $isYearCol = $parentCol && preg_match('/^(19|20)\d{2}$/', trim($parentCol['name'] ?? ''));

                    // Recursive updater for columns
                    $updateTree = function(&$items) use (&$updateTree, $parentColId, $subNames, $applyToAllYears, $isYearCol) {
                        foreach ($items as &$col) {
                            $isTarget = (($col['id'] ?? '') === $parentColId)
                                || ($applyToAllYears && $isYearCol && preg_match('/^(19|20)\d{2}$/', trim($col['name'] ?? '')));

                            if ($isTarget) {
                                if (empty($subNames)) {
                                    unset($col['sub_columns']);
                                } else {
                                    $existingSubs = $col['sub_columns'] ?? [];
                                    $newSubs = [];
                                    
                                    // Peta nama existing ke sub-kolom agar data lama tetap terjaga
                                    $existingMap = [];
                                    $allUsedIds = [];
                                    foreach ($existingSubs as $es) {
                                        $nm = strtolower(trim($es['name'] ?? ''));
                                        if ($nm !== '') {
                                            $existingMap[$nm] = $es;
                                        }
                                        if (!empty($es['id'])) {
                                            $allUsedIds[] = $es['id'];
                                        }
                                    }

                                    foreach ($subNames as $idx => $sName) {
                                        $sTrim = trim($sName);
                                        $sLower = strtolower($sTrim);
                                        $matchedExisting = $existingMap[$sLower] ?? null;

                                        if ($matchedExisting && !empty($matchedExisting['id'])) {
                                            $subId = $matchedExisting['id'];
                                        } else {
                                            // Alokasikan ID baru yang dijamin belum pernah dipakai sama sekali
                                            $counter = 1;
                                            $prefix = ($col['id'] ?? 'col') . '_sub_';
                                            while (in_array($prefix . $counter, $allUsedIds)) {
                                                $counter++;
                                            }
                                            $subId = $prefix . $counter;
                                            $allUsedIds[] = $subId;
                                        }

                                        $isTotal = in_array($sLower, ['jumlah', 'total', 'jml', 'jmlh']);
                                        $subItem = [
                                            'id' => $subId,
                                            'name' => $sTrim,
                                            'type' => 'number',
                                            'is_total' => $isTotal,
                                        ];
                                        if ($matchedExisting && !empty($matchedExisting['sub_columns'])) {
                                            $subItem['sub_columns'] = $matchedExisting['sub_columns'];
                                        }
                                        $newSubs[] = $subItem;
                                    }

                                    // Jamin keunikan ID 100% pada $newSubs untuk mencegah tabrakan ID
                                    $assignedIds = [];
                                    foreach ($newSubs as &$ns) {
                                        if (in_array($ns['id'], $assignedIds)) {
                                            $counter = 1;
                                            $prefix = ($col['id'] ?? 'col') . '_sub_';
                                            while (in_array($prefix . $counter, $assignedIds) || in_array($prefix . $counter, $allUsedIds)) {
                                                $counter++;
                                            }
                                            $ns['id'] = $prefix . $counter;
                                        }
                                        $assignedIds[] = $ns['id'];
                                    }
                                    unset($ns);

                                    $col['sub_columns'] = $newSubs;
                                }
                            } elseif (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                                $updateTree($col['sub_columns']);
                            }
                        }
                        unset($col);
                    };

                    $updateTree($cols);
                    $tbl['columns'] = $cols;
                    break;
                }
            }
            unset($tbl);

            $updatePayload = ['table_configs' => array_values($tables)];
            if ($activeTableId === 1) {
                foreach ($tables as $t) {
                    if ((int) ($t['id'] ?? 0) === 1) {
                        $updatePayload['custom_columns'] = $t['columns'];
                        break;
                    }
                }
            }
            $urusan->update($updatePayload);

            ActivityLog::record(
                'set_sub_columns',
                "Mengatur sub-kolom pada tabel #{$activeTableId} urusan {$urusan->nama_urusan}",
                $urusan
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sub-kolom berhasil disimpan.',
                ]);
            }

            return redirect()->route('urusan.show', [
                'id' => $urusan->slug ?: $urusan->id,
                'tabel' => $activeTableId,
            ])->with('success', 'Sub-kolom berhasil diperbarui.');
        }

        if ($action === 'delete_sub_column') {
            $parentColId = $request->input('parent_col_id');
            $subColId = $request->input('sub_col_id');
            $subColName = trim((string) $request->input('sub_col_name', ''));

            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    $cols = $tbl['columns'] ?? [];

                    $deleteInTree = function(&$items) use (&$deleteInTree, $parentColId, $subColId, $subColName) {
                        foreach ($items as &$col) {
                            $matchParent = empty($parentColId) || (($col['id'] ?? '') === $parentColId);
                            if ($matchParent && !empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                                $beforeCount = count($col['sub_columns']);
                                $col['sub_columns'] = array_values(array_filter($col['sub_columns'], function($s) use ($subColId, $subColName) {
                                    $isTargetId = ($s['id'] ?? '') === $subColId;
                                    if (!$isTargetId) {
                                        return true; // bukan target ID, pertahankan
                                    }
                                    // Jika nama sub-kolom disediakan, hanya hapus jika namanya juga cocok
                                    if ($subColName !== '') {
                                        return strtolower(trim($s['name'] ?? '')) !== strtolower($subColName);
                                    }
                                    return false; // target dihapus
                                }));

                                if (count($col['sub_columns']) < $beforeCount) {
                                    if (empty($col['sub_columns'])) {
                                        unset($col['sub_columns']);
                                    } else {
                                        // Pastikan keunikan ID pada sisa sub-kolom
                                        $seenIds = [];
                                        foreach ($col['sub_columns'] as &$remSub) {
                                            if (in_array($remSub['id'], $seenIds)) {
                                                $cnt = 1;
                                                $pfx = ($col['id'] ?? 'col') . '_sub_';
                                                while (in_array($pfx . $cnt, $seenIds)) {
                                                    $cnt++;
                                                }
                                                $remSub['id'] = $pfx . $cnt;
                                            }
                                            $seenIds[] = $remSub['id'];
                                        }
                                        unset($remSub);
                                    }
                                    return true;
                                }
                            }
                            if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                                if ($deleteInTree($col['sub_columns'])) {
                                    return true;
                                }
                            }
                        }
                        unset($col);
                        return false;
                    };

                    $deleteInTree($cols);
                    $tbl['columns'] = $cols;
                    break;
                }
            }
            unset($tbl);

            $updatePayload = ['table_configs' => array_values($tables)];
            if ($activeTableId === 1) {
                foreach ($tables as $t) {
                    if ((int) ($t['id'] ?? 0) === 1) {
                        $updatePayload['custom_columns'] = $t['columns'];
                        break;
                    }
                }
            }
            $urusan->update($updatePayload);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sub-kolom berhasil dihapus.',
                ]);
            }

            return back()->with('success', 'Sub-kolom berhasil dihapus.');
        }

        if ($action === 'toggle_footer_total') {
            $targetShow = $request->has('show_footer_total') 
                ? $request->boolean('show_footer_total') 
                : null;
            $showFooterJumlah = $request->has('show_footer_jumlah') 
                ? $request->boolean('show_footer_jumlah') 
                : null;
            $showFooterSubtotal = $request->has('show_footer_subtotal') 
                ? $request->boolean('show_footer_subtotal') 
                : null;
            $footerMode = $request->input('footer_mode');

            $newStatus = true;
            $newJumlah = true;
            $newSubtotal = true;

            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    if ($footerMode !== null) {
                        if ($footerMode === 'none') {
                            $tbl['show_footer_total'] = false;
                            $tbl['show_footer_jumlah'] = false;
                            $tbl['show_footer_subtotal'] = false;
                        } elseif ($footerMode === 'jumlah') {
                            $tbl['show_footer_total'] = true;
                            $tbl['show_footer_jumlah'] = true;
                            $tbl['show_footer_subtotal'] = false;
                        } elseif ($footerMode === 'total') {
                            $tbl['show_footer_total'] = true;
                            $tbl['show_footer_jumlah'] = false;
                            $tbl['show_footer_subtotal'] = true;
                        } elseif ($footerMode === 'both') {
                            $tbl['show_footer_total'] = true;
                            $tbl['show_footer_jumlah'] = true;
                            $tbl['show_footer_subtotal'] = true;
                        }
                        $tbl['footer_mode'] = $footerMode;
                    } else {
                        if ($targetShow !== null) {
                            $tbl['show_footer_total'] = $targetShow;
                        } else {
                            $tbl['show_footer_total'] = !($tbl['show_footer_total'] ?? true);
                        }
                        if ($showFooterJumlah !== null) {
                            $tbl['show_footer_jumlah'] = $showFooterJumlah;
                        }
                        if ($showFooterSubtotal !== null) {
                            $tbl['show_footer_subtotal'] = $showFooterSubtotal;
                        }
                    }

                    $newStatus = (bool) ($tbl['show_footer_total'] ?? true);
                    $newJumlah = (bool) ($tbl['show_footer_jumlah'] ?? true);
                    $newSubtotal = (bool) ($tbl['show_footer_subtotal'] ?? true);
                    break;
                }
            }
            unset($tbl);

            $updatePayload = ['table_configs' => array_values($tables)];
            $urusan->update($updatePayload);


            $modeText = 'Jumlah & Total';
            if (!$newStatus || $footerMode === 'none') {
                $modeText = 'Nonaktif';
            } elseif ($footerMode === 'jumlah' || ($newJumlah && !$newSubtotal)) {
                $modeText = 'Jumlah Saja';
            } elseif ($footerMode === 'total' || (!$newJumlah && $newSubtotal)) {
                $modeText = 'Total Saja';
            }

            ActivityLog::record(
                'toggle_footer_total',
                "Mengubah baris bawah tabel menjadi {$modeText} pada tabel #{$activeTableId} urusan {$urusan->nama_urusan}",
                $urusan
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'show_footer_total' => $newStatus,
                    'show_footer_jumlah' => $newJumlah,
                    'show_footer_subtotal' => $newSubtotal,
                    'footer_mode' => $footerMode,
                    'message' => "Pengaturan baris bawah ({$modeText}) berhasil disimpan.",
                ]);
            }

            return back()->with('success', "Pengaturan baris bawah ({$modeText}) berhasil disimpan.");
        }

        if ($action === 'set_footer_override') {
            $colId = $request->input('col_id');
            $val = $request->input('value');
            $footerLabel = $request->input('footer_label');
            $footerSubtotalLabel = $request->input('footer_subtotal_label');
            $showFooterSubtotal = $request->has('show_footer_subtotal') ? $request->boolean('show_footer_subtotal') : null;

            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    if ($footerLabel !== null) {
                        $tbl['footer_label'] = trim($footerLabel) ?: 'Jumlah';
                    }
                    if ($footerSubtotalLabel !== null) {
                        $tbl['footer_subtotal_label'] = trim($footerSubtotalLabel) ?: 'TOTAL';
                    }
                    if ($showFooterSubtotal !== null) {
                        $tbl['show_footer_subtotal'] = $showFooterSubtotal;
                    }
                    if ($colId) {
                        $overrides = $tbl['footer_overrides'] ?? [];
                        if ($val !== null && trim($val) !== '' && strtolower(trim($val)) !== 'auto') {
                            $overrides[$colId] = trim($val);
                        } else {
                            unset($overrides[$colId]);
                        }
                        $tbl['footer_overrides'] = $overrides;
                    }
                    break;
                }
            }
            unset($tbl);

            $updatePayload = ['table_configs' => array_values($tables)];
            $urusan->update($updatePayload);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nilai baris Jumlah berhasil disimpan.',
                ]);
            }

            return back()->with('success', 'Nilai baris Jumlah berhasil disimpan.');
        }

        if ($action === 'rename_table') {
            $newName = trim($request->input('nama_tabel'));
            if (!$newName) {
                return back()->with('error', 'Nama halaman tidak boleh kosong.');
            }

            $renamedYear = $request->filled('tahun') ? (int) $request->input('tahun') : null;
            if ($renamedYear) {
                Tahun::firstOrCreate(
                    ['tahun' => $renamedYear],
                    ['status_aktif' => true, 'is_default' => false]
                );
                session(['selected_tahun' => $renamedYear]);
            }

            foreach ($tables as &$tbl) {
                if ((int) ($tbl['id'] ?? 0) === $activeTableId) {
                    $tbl['name'] = $newName;
                    if ($renamedYear) {
                        $tbl['tahun'] = $renamedYear;
                    }
                    break;
                }
            }
            unset($tbl);

            $updatePayload = ['table_configs' => array_values($tables)];
            $urusan->update($updatePayload);

            ActivityLog::record(
                'rename_table',
                "Mengubah nama halaman tabel #{$activeTableId} menjadi '{$newName}'" . ($renamedYear ? " (Tahun {$renamedYear})" : "") . " pada urusan {$urusan->nama_urusan}",
                $urusan
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nama halaman berhasil diperbarui.',
                    'name' => $newName,
                    'tahun' => $renamedYear,
                ]);
            }

            return redirect()->route('urusan.show', [
                'id' => $urusan->slug ?: $urusan->id,
                'tabel' => $activeTableId,
                'tahun' => $renamedYear ?: session('selected_tahun', Tahun::getDefaultTahun()),
            ])->with('success', 'Nama halaman berhasil diperbarui.');
        }

        return back();
    }

    public function storeTable(Request $request, $id)
    {
        $urusan = is_numeric($id) ? Urusan::findOrFail($id) : Urusan::where('slug', $id)->firstOrFail();

        $namaTabel = trim($request->input('nama_tabel'));
        if (!$namaTabel) {
            $namaTabel = 'Tabel ' . (count($urusan->tables) + 1);
        }

        $numCols = (int) $request->input('num_cols', 3);
        $numRows = (int) $request->input('num_rows', 4);
        $selectedTahunNomor = $request->input('tahun');

        $numCols = max(0, min(20, $numCols));
        $numRows = max(0, min(100, $numRows));

        if ($selectedTahunNomor) {
            $tahunModel = Tahun::firstOrCreate(
                ['tahun' => (int) $selectedTahunNomor],
                ['status_aktif' => true, 'is_default' => false]
            );
            session(['selected_tahun' => (int) $tahunModel->tahun]);
        }
        $activeYear = $selectedTahunNomor ?: session('selected_tahun', Tahun::getDefaultTahun());

        $tables = $urusan->tables;
        $maxConfigId = 0;
        foreach ($tables as $t) {
            if ((int) ($t['id'] ?? 0) > $maxConfigId) {
                $maxConfigId = (int) $t['id'];
            }
        }
        $maxIndTableId = (int) Indikator::where('urusan_id', $urusan->id)->max('table_id');
        $newTableId = max($maxConfigId, $maxIndTableId) + 1;

        $duplicateStructure = (bool) $request->input('duplicate_structure', false);
        $sourceTableId = $request->input('source_table_id');

        DB::transaction(function () use ($urusan, $newTableId, $duplicateStructure, $sourceTableId, $namaTabel, $activeYear, $numCols, $numRows, $request, &$tables) {
            // Bersihkan data lama jika ada indikator dengan newTableId untuk urusan ini agar salinan selalu akurat
            $oldInds = Indikator::where('urusan_id', $urusan->id)->where('table_id', $newTableId)->pluck('id');
            if ($oldInds->isNotEmpty()) {
                DataIndikator::whereIn('indikator_id', $oldInds)->delete();
                Indikator::whereIn('id', $oldInds)->forceDelete();
            }

            if ($duplicateStructure && $sourceTableId) {
                $sourceTableId = (int) $sourceTableId;
                $srcConfig = $urusan->getTableConfig($sourceTableId);
                $newColumns = $srcConfig['columns'] ?? [];

                $tables[] = [
                    'id' => $newTableId,
                    'name' => $namaTabel,
                    'tahun' => (int) $activeYear,
                    'columns' => $newColumns,
                    'show_footer_total' => $srcConfig['show_footer_total'] ?? false,
                    'footer_label' => $srcConfig['footer_label'] ?? 'Jumlah',
                ];

                $urusan->update([
                    'table_configs' => array_values($tables),
                ]);

                // Salin seluruh indikator (dengan relasi parent-child terjaga)
                $sourceRoots = Indikator::where('urusan_id', $urusan->id)
                    ->where(function ($q) use ($sourceTableId) {
                        if ($sourceTableId === 1) {
                            $q->where('table_id', 1)->orWhereNull('table_id');
                        } else {
                            $q->where('table_id', $sourceTableId);
                        }
                    })
                    ->whereNull('parent_id')
                    ->orderBy('urutan')
                    ->get();

                $targetTahunModel = Tahun::firstOrCreate(
                    ['tahun' => (int) $activeYear],
                    ['status_aktif' => true, 'is_default' => false]
                );
                $srcYear = $srcConfig['tahun'] ?? null;
                $srcTahunModel = $srcYear ? Tahun::where('tahun', (int) $srcYear)->first() : null;

                $cloneIndikator = function ($sourceInd, $newParentId = null) use (&$cloneIndikator, $urusan, $newTableId, $targetTahunModel, $srcTahunModel) {
                    $newInd = Indikator::create([
                        'urusan_id' => $urusan->id,
                        'table_id' => $newTableId,
                        'parent_id' => $newParentId,
                        'kode_indikator' => $sourceInd->kode_indikator,
                        'nama_indikator' => $sourceInd->nama_indikator,
                        'deskripsi' => $sourceInd->deskripsi,
                        'satuan' => $sourceInd->satuan,
                        'tipe_data' => $sourceInd->tipe_data,
                        'urutan' => $sourceInd->urutan,
                        'status_aktif' => $sourceInd->status_aktif,
                    ]);

                    // Salin isi data (DataIndikator) dari tahun sebelumnya ke tahun baru
                    $srcData = null;
                    if ($srcTahunModel) {
                        $srcData = DataIndikator::where('indikator_id', $sourceInd->id)
                            ->where('tahun_id', $srcTahunModel->id)
                            ->first();
                    }
                    if (!$srcData) {
                        $srcData = DataIndikator::where('indikator_id', $sourceInd->id)
                            ->latest()
                            ->first();
                    }

                    if ($srcData) {
                        DataIndikator::create([
                            'indikator_id' => $newInd->id,
                            'tahun_id' => $targetTahunModel->id,
                            'nilai' => $srcData->nilai,
                            'sumber_data' => $srcData->sumber_data,
                            'keterangan' => $srcData->keterangan,
                            'custom_fields' => $srcData->custom_fields,
                            'updated_by' => auth()->id() ?? $srcData->updated_by,
                        ]);
                    }

                    $children = Indikator::where('parent_id', $sourceInd->id)->orderBy('urutan')->get();
                    foreach ($children as $child) {
                        $cloneIndikator($child, $newInd->id);
                    }
                };

                foreach ($sourceRoots as $root) {
                    $cloneIndikator($root, null);
                }
            } else {
                $colNames = $request->input('col_names', []);
                $colTypes = $request->input('col_types', []);

                $newColumns = [];
                for ($i = 0; $i < $numCols; $i++) {
                    $name = isset($colNames[$i]) && trim($colNames[$i]) !== '' ? trim($colNames[$i]) : ('Kolom ' . ($i + 1));
                    $type = isset($colTypes[$i]) ? $colTypes[$i] : 'text';
                    $cId = 'col_' . time() . '_' . ($i + 1);

                    $newColumns[] = [
                        'id' => $cId,
                        'name' => $name,
                        'type' => $type,
                    ];
                }

                $tables[] = [
                    'id' => $newTableId,
                    'name' => $namaTabel,
                    'tahun' => (int) $activeYear,
                    'columns' => $newColumns,
                ];

                $urusan->update([
                    'table_configs' => array_values($tables),
                ]);

                // Buat baris indikator awal untuk tabel baru
                for ($r = 1; $r <= $numRows; $r++) {
                    Indikator::create([
                        'urusan_id' => $urusan->id,
                        'table_id' => $newTableId,
                        'parent_id' => null,
                        'nama_indikator' => (string) $r,
                        'tipe_data' => 'numerik',
                        'urutan' => $r,
                        'status_aktif' => true,
                    ]);
                }
            }

            ActivityLog::record(
                'create_table',
                "Membuat {$namaTabel} pada urusan {$urusan->nama_urusan} ({$numCols} kolom, {$numRows} baris)",
                $urusan
            );
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Halaman {$namaTabel} berhasil dibuat!",
                'redirect_url' => route('urusan.show', [
                    'id' => $urusan->slug ?: $urusan->id,
                    'tabel' => $newTableId,
                    'tahun' => $activeYear,
                ]),
            ]);
        }

        return redirect()->route('urusan.show', [
            'id' => $urusan->slug ?: $urusan->id,
            'tabel' => $newTableId,
            'tahun' => $activeYear,
        ])->with('success', "Halaman {$namaTabel} berhasil dibuat!");
    }

    public function destroyTable(Request $request, $id, $tableId)
    {
        $urusan = is_numeric($id) ? Urusan::findOrFail($id) : Urusan::where('slug', $id)->firstOrFail();
        $tableId = (int) $tableId;

        $tables = $urusan->tables;
        if (count($tables) <= 1) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus satu-satunya tabel yang ada.',
                ], 422);
            }
            return back()->with('error', 'Tidak dapat menghapus satu-satunya tabel yang ada.');
        }

        $nextTableId = 1;

        DB::transaction(function () use ($urusan, $tableId, $tables, &$nextTableId) {
            // Hapus indikator dan data indikator milik tabel ini
            $indikatorIds = Indikator::where('urusan_id', $urusan->id)
                ->where(function ($q) use ($tableId) {
                    if ($tableId === 1) {
                        $q->where('table_id', 1)->orWhereNull('table_id');
                    } else {
                        $q->where('table_id', $tableId);
                    }
                })
                ->pluck('id');

            DataIndikator::whereIn('indikator_id', $indikatorIds)->delete();
            Indikator::whereIn('id', $indikatorIds)->forceDelete();

            // Hapus tabel dari konfigurasi
            $filtered = array_values(array_filter($tables, fn($t) => (int) ($t['id'] ?? 0) !== $tableId));
            $urusan->update(['table_configs' => $filtered]);

            $nextTableId = $filtered[0]['id'] ?? 1;

            ActivityLog::record(
                'delete_table',
                "Menghapus tabel #{$tableId} pada urusan {$urusan->nama_urusan}",
                $urusan
            );
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Halaman tabel berhasil dihapus.',
                'redirect_url' => route('urusan.show', [
                    'id' => $urusan->slug ?: $urusan->id,
                    'tabel' => $nextTableId,
                    'tahun' => session('selected_tahun', Tahun::getDefaultTahun()),
                ]),
            ]);
        }

        return redirect()->route('urusan.show', [
            'id' => $urusan->slug ?: $urusan->id,
            'tabel' => $nextTableId,
            'tahun' => session('selected_tahun', Tahun::getDefaultTahun()),
        ])->with('success', 'Halaman tabel berhasil dihapus.');
    }
}

