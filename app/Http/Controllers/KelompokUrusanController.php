<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KelompokUrusan;
use App\Models\ActivityLog;
use App\Models\Tahun;
use App\Models\Indikator;
use App\Models\DataIndikator;
use App\Services\DataKelengkapanEvaluator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KelompokUrusanController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:kelompok_urusan,id',
            'kode' => 'nullable|string|max:10|unique:kelompok_urusan,kode',
            'nama' => 'required|string|max:255',
            'urutan' => 'nullable|integer',
        ], [
            'kode.unique' => 'Kode kelompok urusan ini sudah digunakan.',
            'nama.required' => 'Nama kelompok urusan wajib diisi.',
        ]);

        // Anti-spam / duplicate prevention: if identical folder was created in the same parent within last 5 seconds, reuse it
        $existingRecent = KelompokUrusan::where(function ($q) use ($validated) {
                if (!empty($validated['parent_id'])) {
                    $q->where('parent_id', $validated['parent_id']);
                } else {
                    $q->whereNull('parent_id');
                }
            })
            ->where('nama', $validated['nama'])
            ->where('created_at', '>=', now()->subSeconds(5))
            ->first();

        if ($existingRecent) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Folder "' . $existingRecent->nama . '" berhasil ditambahkan.',
                    'kelompok' => [
                        'id' => $existingRecent->id,
                        'parent_id' => $existingRecent->parent_id,
                        'kode' => $existingRecent->kode,
                        'nama' => $existingRecent->nama,
                        'keterangan' => $existingRecent->keterangan,
                    ],
                ]);
            }

            return back()->with('success', 'Kelompok urusan [' . $existingRecent->kode . '] ' . $existingRecent->nama . ' berhasil ditambahkan.')
                ->with('created_kelompok_id', $existingRecent->id);
        }

        if (empty($validated['kode'])) {
            $sequence = 1;
            do {
                $validated['kode'] = 'N' . $sequence++;
            } while (KelompokUrusan::where('kode', $validated['kode'])->exists());
        }

        if (empty($validated['urutan'])) {
            $validated['urutan'] = (KelompokUrusan::where('parent_id', $validated['parent_id'] ?? null)->max('urutan') ?? 0) + 1;
        }

        $kelompok = KelompokUrusan::create($validated);

        ActivityLog::record(
            'create_kelompok_urusan',
            'Menambahkan kelompok urusan baru: [' . $kelompok->kode . '] ' . $kelompok->nama,
            $kelompok
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Folder "' . $kelompok->nama . '" berhasil ditambahkan.',
                'kelompok' => [
                    'id' => $kelompok->id,
                    'parent_id' => $kelompok->parent_id,
                    'kode' => $kelompok->kode,
                    'nama' => $kelompok->nama,
                    'keterangan' => $kelompok->keterangan,
                ],
            ]);
        }

        return back()->with('success', 'Kelompok urusan [' . $kelompok->kode . '] ' . $kelompok->nama . ' berhasil ditambahkan.')
            ->with('created_kelompok_id', $kelompok->id);
    }

    public function update(Request $request, $id)
    {
        $kelompok = KelompokUrusan::findOrFail($id);
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', Rule::unique('kelompok_urusan', 'kode')->ignore($kelompok->id)],
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ], [
            'kode.required' => 'Kode kelompok urusan wajib diisi.',
            'kode.unique' => 'Kode kelompok urusan ini sudah digunakan.',
            'nama.required' => 'Nama kelompok urusan wajib diisi.',
        ]);

        $kelompok->update($validated);
        ActivityLog::record(
            'update_kelompok_urusan',
            'Memperbarui kelompok urusan: [' . $kelompok->kode . '] ' . $kelompok->nama,
            $kelompok
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Folder "' . $kelompok->nama . '" berhasil diperbarui.',
                'kelompok' => $kelompok,
            ]);
        }

        return back()->with('success', 'Kelompok urusan [' . $kelompok->kode . '] ' . $kelompok->nama . ' berhasil diperbarui.')
            ->with('created_kelompok_id', $kelompok->id);
    }

    public function destroy(Request $request, $id)
    {
        $kelompok = KelompokUrusan::findOrFail($id);
        $nama = $kelompok->nama;
        $kode = $kelompok->kode;
        $kelompokId = $kelompok->id;
        $kelompokIds = collect([$kelompokId]);
        $frontier = collect([$kelompokId]);
        while ($frontier->isNotEmpty()) {
            $children = KelompokUrusan::whereIn('parent_id', $frontier)->pluck('id');
            $frontier = $children->diff($kelompokIds)->values();
            $kelompokIds = $kelompokIds->merge($frontier);
        }
        $urusanIds = DB::table('urusan')->whereIn('kelompok_urusan_id', $kelompokIds)->pluck('id');
        $urusanCount = $urusanIds->count();
        $message = 'Folder "' . $nama . '" berhasil dihapus.';

        DB::transaction(function () use ($kelompok, $kelompokIds, $urusanIds, $nama, $kode, $urusanCount) {
            $indikatorIds = DB::table('indikator')
                ->whereIn('urusan_id', $urusanIds)
                ->select('id');

            DB::table('data_indikator')
                ->whereIn('indikator_id', $indikatorIds)
                ->delete();
            DB::table('indikator')
                ->whereIn('urusan_id', $urusanIds)
                ->update(['parent_id' => null]);
            DB::table('indikator')
                ->whereIn('urusan_id', $urusanIds)
                ->delete();
            DB::table('urusan')
                ->whereIn('kelompok_urusan_id', $kelompokIds)
                ->delete();

            DB::table('kelompok_urusan')->whereIn('id', $kelompokIds)->delete();
            ActivityLog::record(
                'delete_kelompok_urusan',
                'Menghapus kelompok urusan: [' . $kode . '] ' . $nama . ' beserta ' . $kelompokIds->count() . ' folder dan ' . $urusanCount . ' urusan.'
            );
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Menampilkan katalog daftar urusan/indikator dalam suatu bidang (sub-urusan).
     */
    public function show(Request $request, $id)
    {
        $kelompok = KelompokUrusan::with(['parent', 'children', 'urusans' => function ($q) {
            $q->where('status_aktif', true)->orderBy('urutan');
        }])->findOrFail($id);

        // 1. Tentukan tahun terpilih
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

        $selectedTahunNomor = $tahun ? (int) $tahun->tahun : (Tahun::getDefaultTahun() ?: 2026);
        session(['selected_tahun' => $selectedTahunNomor]);

        // 2. Evaluasi statistik untuk masing-masing urusan di bawah bidang ini
        $urusanIds = $kelompok->urusans->pluck('id');
        $leafIndikators = Indikator::whereIn('urusan_id', $urusanIds)
            ->where('status_aktif', true)
            ->doesntHave('children')
            ->with(['urusan', 'dataIndikators.tahun'])
            ->get();

        $allDataIndikator = DataIndikator::whereIn('indikator_id', $leafIndikators->pluck('id'))
            ->with('tahun')
            ->get();

        $leafCountsByUrusan = $leafIndikators->groupBy('urusan_id');

        $totalRincianBidang = $leafIndikators->count();
        $totalTerisiBidang = 0;

        $daftarUrusan = $kelompok->urusans->map(function ($u) use ($leafCountsByUrusan, $allDataIndikator, $selectedTahunNomor, &$totalTerisiBidang) {
            $leaves = $leafCountsByUrusan->get($u->id, collect());
            $rCount = $leaves->count();
            $tCount = 0;

            foreach ($leaves as $leaf) {
                if (DataKelengkapanEvaluator::isFilledForYear($leaf, $selectedTahunNomor, $allDataIndikator)) {
                    $tCount++;
                    $totalTerisiBidang++;
                }
            }

            $pct = $rCount > 0 ? round(($tCount / $rCount) * 100, 1) : 0;
            $tableCount = count($u->tables);

            return [
                'id' => $u->id,
                'nama' => $u->nama_urusan,
                'slug' => $u->slug ?: $u->id,
                'icon' => $u->icon ?: 'bi-table',
                'deskripsi' => $u->deskripsi ?? '',
                'total_tabel' => $tableCount > 0 ? $tableCount : 1,
                'total_rincian' => $rCount,
                'total_terisi' => $tCount,
                'persentase' => min($pct, 100),
            ];
        });

        $persentaseBidang = $totalRincianBidang > 0 ? round(($totalTerisiBidang / $totalRincianBidang) * 100, 1) : 0;
        $listTahuns = Tahun::syncActiveYearsFromSystem();

        return view('kelompok-urusan.show', compact(
            'kelompok',
            'tahun',
            'selectedTahunNomor',
            'daftarUrusan',
            'totalRincianBidang',
            'totalTerisiBidang',
            'persentaseBidang',
            'listTahuns'
        ));
    }
}
