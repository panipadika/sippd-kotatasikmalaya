<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KelompokUrusan;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\Tahun;
use App\Models\DataIndikator;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DataNilaiController extends Controller
{
    public function index(Request $request)
    {
        $selectedKelompokId = $request->get('kelompok_id');
        $selectedUrusanId = $request->get('urusan_id');
        $selectedTahunNomor = $request->get('tahun', session('selected_tahun', Tahun::getDefaultTahun()));
        $search = $request->get('search');

        $tahun = Tahun::where('tahun', $selectedTahunNomor)->first() ?: Tahun::where('is_default', true)->first();
        if ($tahun) {
            session(['selected_tahun' => $tahun->tahun]);
            $selectedTahunId = $tahun->id;
        } else {
            $selectedTahunId = null;
        }

        $kelompoks = KelompokUrusan::orderBy('urutan')->get();

        // If kelompok is selected, filter urusans for the dropdown
        $urusansQuery = Urusan::where('status_aktif', true)->orderBy('urutan');
        if ($selectedKelompokId) {
            $urusansQuery->where('kelompok_urusan_id', $selectedKelompokId);
        }
        $availableUrusans = $urusansQuery->get();

        // If no urusan selected yet, take first available urusan if wanted or query across
        $currentUrusan = null;
        if ($selectedUrusanId) {
            $currentUrusan = Urusan::with('kelompokUrusan')->find($selectedUrusanId);
        } elseif ($availableUrusans->count() > 0) {
            $currentUrusan = $availableUrusans->first();
            $selectedUrusanId = $currentUrusan->id;
        }

        $indicatorsList = [];
        $totalIndikator = 0;
        $totalTerisi = 0;

        if ($currentUrusan) {
            // Get hierarchical indicators for this urusan
            $rootQuery = Indikator::where('urusan_id', $currentUrusan->id)
                ->whereNull('parent_id')
                ->where('status_aktif', true)
                ->orderBy('urutan');

            if ($search) {
                $matchingIds = Indikator::where('urusan_id', $currentUrusan->id)
                    ->where('status_aktif', true)
                    ->where(function ($q) use ($search) {
                        $q->where('nama_indikator', 'like', "%{$search}%")
                          ->orWhere('kode_indikator', 'like', "%{$search}%");
                    })->pluck('id');

                $ancestorIds = [];
                foreach (Indikator::whereIn('id', $matchingIds)->get() as $item) {
                    foreach ($item->ancestors as $anc) {
                        $ancestorIds[] = $anc->id;
                    }
                }
                $visibleIds = array_unique(array_merge($matchingIds->toArray(), $ancestorIds));

                $rootIndicators = Indikator::where('urusan_id', $currentUrusan->id)
                    ->whereNull('parent_id')
                    ->whereIn('id', $visibleIds)
                    ->with(['allChildren', 'dataIndikators' => function ($q) use ($selectedTahunId) {
                        if ($selectedTahunId) {
                            $q->where('tahun_id', $selectedTahunId);
                        }
                    }])
                    ->orderBy('urutan')
                    ->get();
            } else {
                $rootIndicators = $rootQuery
                    ->with(['allChildren', 'dataIndikators' => function ($q) use ($selectedTahunId) {
                        if ($selectedTahunId) {
                            $q->where('tahun_id', $selectedTahunId);
                        }
                    }])
                    ->get();
            }

            // Recursive function to flatten tree
            $flatten = function ($items, $level = 0) use (&$flatten, &$indicatorsList, $selectedTahunId) {
                foreach ($items as $item) {
                    $item->current_data = $selectedTahunId ? $item->dataIndikators->firstWhere('tahun_id', $selectedTahunId) : null;
                    $item->tree_level = $level;
                    $indicatorsList[] = $item;

                    if ($item->children && $item->children->count() > 0) {
                        $flatten($item->children, $level + 1);
                    }
                }
            };

            $loadRecursively = function ($items) use (&$loadRecursively, $selectedTahunId) {
                foreach ($items as $item) {
                    $item->load(['children' => function ($q) use ($selectedTahunId) {
                        $q->where('status_aktif', true)->orderBy('urutan')->with(['dataIndikators' => function ($dq) use ($selectedTahunId) {
                            if ($selectedTahunId) {
                                $dq->where('tahun_id', $selectedTahunId);
                            }
                        }]);
                    }]);
                    if ($item->children && $item->children->count() > 0) {
                        $loadRecursively($item->children);
                    }
                }
            };

            $loadRecursively($rootIndicators);
            $flatten($rootIndicators);

            $totalIndikator = count($indicatorsList);
            $totalTerisi = collect($indicatorsList)->filter(function ($i) {
                return $i->current_data && $i->current_data->nilai !== null && $i->current_data->nilai !== '';
            })->count();
        }

        $persentase = $totalIndikator > 0 ? round(($totalTerisi / $totalIndikator) * 100, 1) : 0;

        return view('data.index', compact(
            'kelompoks',
            'availableUrusans',
            'currentUrusan',
            'tahun',
            'selectedKelompokId',
            'selectedUrusanId',
            'selectedTahunNomor',
            'indicatorsList',
            'totalIndikator',
            'totalTerisi',
            'persentase',
            'search'
        ));
    }

    public function updateSingle(Request $request)
    {
        $request->validate([
            'indikator_id' => 'required|exists:indikator,id',
            'tahun_id' => 'required|exists:tahun,id',
            'nilai' => 'nullable|string|max:255',
            'sumber_data' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $indikator = Indikator::findOrFail($request->indikator_id);
        $tahun = Tahun::findOrFail($request->tahun_id);

        $nilai = $request->nilai;
        if ($nilai !== null && trim($nilai) !== '') {
            $nilai = trim($nilai);
            // Replace comma with dot for Indonesian decimal notation if needed
            $normalized = str_replace(',', '.', $nilai);
            if (in_array($indikator->tipe_data, ['numerik', 'persentase'])) {
                if (!is_numeric($normalized)) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Nilai harus berupa angka valid untuk jenis indikator ' . $indikator->tipe_data . '.'], 422);
                    }
                    return back()->with('error', 'Nilai harus berupa angka valid untuk jenis indikator ' . $indikator->tipe_data . '.');
                }
                $nilai = $normalized;
            }
        } else {
            $nilai = null;
        }

        $existing = DataIndikator::where('indikator_id', $indikator->id)
            ->where('tahun_id', $tahun->id)
            ->first();

        $customFields = $existing ? ($existing->custom_fields ?? []) : [];
        if ($request->has('custom_fields') && is_array($request->custom_fields)) {
            $customFields = array_merge($customFields, $request->custom_fields);
        }

        $data = DataIndikator::updateOrCreate(
            [
                'indikator_id' => $indikator->id,
                'tahun_id' => $tahun->id,
            ],
            [
                'nilai' => $request->has('nilai') ? $nilai : ($existing->nilai ?? null),
                'sumber_data' => $request->has('sumber_data') ? $request->sumber_data : ($existing->sumber_data ?? null),
                'keterangan' => $request->has('keterangan') ? $request->keterangan : ($existing->keterangan ?? null),
                'custom_fields' => !empty($customFields) ? $customFields : null,
                'updated_by' => Auth::id(),
            ]
        );

        ActivityLog::record(
            'update_nilai',
            'Mengubah nilai data indikator "' . $indikator->nama_indikator . '" tahun ' . $tahun->tahun . ' menjadi: ' . ($nilai ?? '(kosong)'),
            $data
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil disimpan!',
                'data' => [
                    'id' => $data->id,
                    'nilai' => $data->nilai,
                    'formatted_nilai' => $data->formatted_nilai,
                    'sumber_data' => $data->sumber_data,
                    'keterangan' => $data->keterangan,
                    'updated_at' => $data->updated_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Nilai indikator "' . $indikator->nama_indikator . '" tahun ' . $tahun->tahun . ' berhasil diperbarui.');
    }

    public function batchUpdate(Request $request)
    {
        $request->validate([
            'tahun_id' => 'required|exists:tahun,id',
            'data' => 'required|array',
        ]);

        $tahun = Tahun::findOrFail($request->tahun_id);
        $savedCount = 0;

        foreach ($request->data as $indikatorId => $payload) {
            $indikator = Indikator::find($indikatorId);
            if (!$indikator) {
                continue;
            }

            $rawNilai = isset($payload['nilai']) ? trim($payload['nilai']) : null;
            if ($rawNilai !== '' && $rawNilai !== null) {
                $normalized = str_replace(',', '.', $rawNilai);
                if (in_array($indikator->tipe_data, ['numerik', 'persentase']) && !is_numeric($normalized)) {
                    continue; // Skip invalid non-numeric
                }
                $nilai = in_array($indikator->tipe_data, ['numerik', 'persentase']) ? $normalized : $rawNilai;
            } else {
                $nilai = null;
            }

            DataIndikator::updateOrCreate(
                [
                    'indikator_id' => $indikator->id,
                    'tahun_id' => $tahun->id,
                ],
                [
                    'nilai' => $nilai,
                    'sumber_data' => $payload['sumber_data'] ?? null,
                    'keterangan' => $payload['keterangan'] ?? null,
                    'updated_by' => Auth::id(),
                ]
            );
            $savedCount++;
        }

        ActivityLog::record(
            'batch_update_nilai',
            'Memperbarui secara massal ' . $savedCount . ' nilai data untuk tahun ' . $tahun->tahun
        );

        return back()->with('success', 'Berhasil memperbarui data untuk ' . $savedCount . ' indikator tahun ' . $tahun->tahun . '.');
    }
}
