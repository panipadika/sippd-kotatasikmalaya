<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indikator;
use App\Models\Urusan;
use App\Models\Tahun;
use App\Models\DataIndikator;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class IndikatorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'urusan_id' => 'required|exists:urusan,id',
            'parent_id' => 'nullable|exists:indikator,id',
            'kode_indikator' => 'nullable|string|max:100',
            'nama_indikator' => 'required|string|max:1000',
            'deskripsi' => 'nullable|string',
            'satuan' => 'nullable|string|max:100',
            'tipe_data' => 'nullable|in:numerik,persentase,teks',
            'urutan' => 'nullable|integer',
        ], [
            'nama_indikator.required' => 'Nama indikator wajib diisi.',
        ]);

        if (empty($validated['tipe_data'])) {
            $validated['tipe_data'] = 'numerik';
        }

        // Auto-assign order if not provided
        if (empty($validated['urutan'])) {
            $lastOrder = Indikator::where('urusan_id', $validated['urusan_id'])
                ->where('parent_id', $validated['parent_id'] ?? null)
                ->max('urutan');
            $validated['urutan'] = ($lastOrder ?? 0) + 1;
        }

        $validated['status_aktif'] = true;

        // Anti-spam: prevent duplicate indicator in rapid succession (< 4 seconds)
        $recentInd = Indikator::where('urusan_id', $validated['urusan_id'])
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->where('nama_indikator', $validated['nama_indikator'])
            ->where('created_at', '>=', now()->subSeconds(4))
            ->first();

        if ($recentInd) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Indikator "' . $recentInd->nama_indikator . '" berhasil ditambahkan!',
                    'indikator' => $recentInd,
                ]);
            }
            return back()->with('success', 'Indikator "' . $recentInd->nama_indikator . '" berhasil ditambahkan.');
        }

        $indikator = Indikator::create($validated);

        // Check if data value, sumber_data, or custom_fields are supplied
        $tahunId = $request->input('tahun_id');
        if (!$tahunId && session('selected_tahun')) {
            $th = Tahun::where('tahun', session('selected_tahun'))->first();
            $tahunId = $th ? $th->id : null;
        }
        if (!$tahunId) {
            $thDefault = Tahun::where('is_default', true)->first();
            $tahunId = $thDefault ? $thDefault->id : null;
        }

        if ($tahunId) {
            $nilai = $request->input('nilai');
            if ($nilai !== null && trim($nilai) !== '') {
                $nilai = trim($nilai);
                $normalized = str_replace(',', '.', $nilai);
                if (in_array($indikator->tipe_data, ['numerik', 'persentase']) && is_numeric($normalized)) {
                    $nilai = $normalized;
                }
            } else {
                $nilai = null;
            }

            $sumberData = $request->input('sumber_data');
            $customFields = $request->input('custom_fields');
            $keterangan = $request->input('keterangan');

            if ($nilai !== null || $sumberData !== null || !empty($customFields) || $keterangan !== null) {
                DataIndikator::create([
                    'indikator_id' => $indikator->id,
                    'tahun_id' => $tahunId,
                    'nilai' => $nilai,
                    'sumber_data' => $sumberData,
                    'keterangan' => $keterangan,
                    'custom_fields' => is_array($customFields) ? $customFields : null,
                    'updated_by' => auth()->id(),
                ]);
            }
        }

        $parentName = $indikator->parent ? ' pada parent "' . $indikator->parent->nama_indikator . '"' : ' (Indikator Utama)';
        ActivityLog::record(
            'create_indikator',
            'Menambahkan indikator: ' . $indikator->nama_indikator . $parentName . ' di urusan ' . $indikator->urusan->nama_urusan,
            $indikator
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Indikator "' . $indikator->nama_indikator . '" berhasil ditambahkan!',
                'indikator' => $indikator,
            ]);
        }

        return back()->with('success', 'Indikator "' . $indikator->nama_indikator . '" berhasil ditambahkan.');
    }

    public function show($id)
    {
        $indikator = Indikator::with(['urusan', 'parent', 'children', 'dataIndikators.tahun'])->findOrFail($id);

        $tahuns = Tahun::where('status_aktif', true)->orderBy('tahun')->get();
        $historyData = [];
        foreach ($tahuns as $t) {
            $data = $indikator->dataIndikators->firstWhere('tahun_id', $t->id);
            $historyData[] = [
                'tahun' => $t->tahun,
                'tahun_id' => $t->id,
                'nilai' => $data ? $data->nilai : null,
                'sumber_data' => $data ? $data->sumber_data : null,
                'keterangan' => $data ? $data->keterangan : null,
                'custom_fields' => $data ? $data->custom_fields : null,
                'updated_at' => $data ? $data->updated_at->format('d/m/Y H:i') : null,
                'updated_by' => $data && $data->updatedByUser ? $data->updatedByUser->name : null,
            ];
        }

        return response()->json([
            'success' => true,
            'indikator' => $indikator,
            'ancestors' => $indikator->ancestors,
            'history' => $historyData,
        ]);
    }

    public function update(Request $request, $id)
    {
        $indikator = Indikator::findOrFail($id);

        $validated = $request->validate([
            'kode_indikator' => 'nullable|string|max:100',
            'nama_indikator' => 'required|string|max:1000',
            'deskripsi' => 'nullable|string',
            'satuan' => 'nullable|string|max:100',
            'tipe_data' => 'required|in:numerik,persentase,teks',
            'urutan' => 'nullable|integer',
            'parent_id' => 'nullable|exists:indikator,id',
            'status_aktif' => 'nullable|boolean',
        ], [
            'nama_indikator.required' => 'Nama indikator wajib diisi.',
        ]);

        // Prevent setting itself or one of its descendants as parent
        if (!empty($validated['parent_id'])) {
            if ($validated['parent_id'] == $indikator->id) {
                return back()->with('error', 'Indikator tidak dapat menjadi induk bagi dirinya sendiri.');
            }
        }

        $validated['status_aktif'] = $request->has('status_aktif');
        $indikator->update($validated);

        ActivityLog::record(
            'update_indikator',
            'Memperbarui indikator: ' . $indikator->nama_indikator,
            $indikator
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Indikator berhasil diperbarui!',
                'indikator' => $indikator,
            ]);
        }

        return back()->with('success', 'Indikator "' . $indikator->nama_indikator . '" berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $indikator = Indikator::findOrFail($id);
        $indikator->status_aktif = !$indikator->status_aktif;
        $indikator->save();

        $statusLabel = $indikator->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::record(
            'toggle_status_indikator',
            'Indikator "' . $indikator->nama_indikator . '" ' . $statusLabel,
            $indikator
        );

        return back()->with('success', 'Indikator "' . $indikator->nama_indikator . '" berhasil ' . $statusLabel . '.');
    }

    public function destroy(Request $request, $id)
    {
        $indikator = Indikator::findOrFail($id);

        // Rule: check if has children
        if ($indikator->children()->count() > 0) {
            if ($request->boolean('force') || $request->input('force') === '1') {
                foreach ($indikator->children as $child) {
                    $child->dataIndikators()->delete();
                    $child->delete();
                }
            } else {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Indikator ini memiliki ' . $indikator->children()->count() . ' sub-baris. Hapus sub-barisnya terlebih dahulu atau pilih hapus semua.',
                        'has_children' => true,
                    ], 422);
                }
                return back()->with('error', 'Indikator tidak dapat dihapus karena masih memiliki ' . $indikator->children()->count() . ' subindikator. Hapus subindikator terlebih dahulu.');
            }
        }

        $indikator->dataIndikators()->delete();
        $nama = $indikator->nama_indikator;
        $uId = $indikator->urusan_id;
        $tId = $indikator->table_id ?? 1;
        $indikator->delete();

        ActivityLog::record(
            'delete_indikator',
            'Menghapus indikator: ' . $nama
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Baris "' . $nama . '" berhasil dihapus.',
                'redirect_url' => url()->previous() ?: route('urusan.show', ['id' => $uId, 'tabel' => $tId]),
            ]);
        }

        return back()->with('success', 'Indikator "' . $nama . '" berhasil dihapus.');
    }

    public function updateCell(Request $request, $id)
    {
        $indikator = Indikator::findOrFail($id);

        $field = $request->input('field');
        $value = $request->input('value');
        $tahunId = $request->input('tahun_id');

        if (!$tahunId && session('selected_tahun')) {
            $th = Tahun::where('tahun', session('selected_tahun'))->first();
            $tahunId = $th ? $th->id : null;
        }
        if (!$tahunId) {
            $thDefault = Tahun::where('is_default', true)->first();
            $tahunId = $thDefault ? $thDefault->id : 1;
        }

        $lowerField = strtolower($field);
        $tConfig = $indikator->urusan->getTableConfig($indikator->table_id ?? 1);
        $tCols = collect($tConfig['columns'] ?? []);
        $colDef = $tCols->firstWhere('id', $field);
        if (!$colDef) {
            foreach ($tCols as $parentC) {
                if (!empty($parentC['sub_columns'])) {
                    foreach ($parentC['sub_columns'] as $subC) {
                        if (($subC['id'] ?? '') === $field) {
                            $colDef = $parentC;
                            break 2;
                        }
                    }
                }
            }
        }

        // Selaraskan tahun penyimpanan data dengan tahun tabel, tahun baris, atau tahun kolom
        if (!empty($tConfig['tahun'])) {
            $thTable = Tahun::autoRegisterYear((int) $tConfig['tahun']);
            if ($thTable) {
                $tahunId = $thTable->id;
            }
        } elseif (preg_match('/^(19|20)\d{2}$/', trim($indikator->nama_indikator), $mRowYear)) {
            $thRow = Tahun::autoRegisterYear((int) $mRowYear[0]);
            if ($thRow) {
                $tahunId = $thRow->id;
            }
        } elseif ($colDef && preg_match('/\b(20\d{2})\b/', trim($colDef['name'] ?? ''), $mColYear)) {
            $thCol = Tahun::autoRegisterYear((int) $mColYear[0]);
            if ($thCol) {
                $tahunId = $thCol->id;
            }
        }

        // Find primary descriptor column in table (first non-No text column without sub-columns, or first non-No column)
        $firstDescriptorCol = $tCols->first(function ($c) {
            $ln = strtolower(trim($c['name'] ?? ''));
            return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']) && ($c['type'] ?? 'text') !== 'number';
        });
        if (!$firstDescriptorCol) {
            $firstDescriptorCol = $tCols->first(function ($c) {
                $ln = strtolower(trim($c['name'] ?? ''));
                return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']);
            });
        }
        $firstDescriptorColId = $firstDescriptorCol['id'] ?? null;

        $isDescriptor = in_array($field, ['nama_indikator', 'col_nama']) 
            || in_array($lowerField, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian', 'tahun', 'thn', 'periode'])
            || ($colDef && in_array(strtolower(trim($colDef['name'] ?? '')), ['uraian', 'nama', 'indikator', 'nama indikator', 'tahun', 'thn', 'periode']))
            || ($firstDescriptorColId && $field === $firstDescriptorColId);

        if ($isDescriptor) {
            $valTrim = trim($value ?? '');
            $indikator->nama_indikator = $valTrim;
            $indikator->save();

            // Persist to custom_fields of active data record so custom column renderer reads it reliably
            $dataForUraian = DataIndikator::firstOrNew([
                'indikator_id' => $indikator->id,
                'tahun_id' => $tahunId,
            ]);
            $cFields = $dataForUraian->custom_fields ?? [];
            if (!is_array($cFields)) $cFields = [];
            $cFields[$field] = $valTrim;
            $dataForUraian->custom_fields = $cFields;
            $dataForUraian->updated_by = auth()->id() ?? 1;
            $dataForUraian->save();

            // Also keep custom_fields in sync across other dataIndikators for this indicator
            foreach ($indikator->dataIndikators as $di) {
                if ($di->id !== $dataForUraian->id) {
                    $diCustom = $di->custom_fields ?? [];
                    if (!is_array($diCustom)) $diCustom = [];
                    $diCustom[$field] = $valTrim;
                    $di->custom_fields = $diCustom;
                    $di->save();
                }
            }

            return response()->json([
                'success' => true,
                'value' => $valTrim,
                'display' => $valTrim !== '' ? $valTrim : '<span class="text-muted fst-italic opacity-40">+ Isi</span>',
            ]);
        }

        if ($field === 'kode_indikator') {
            $indikator->kode_indikator = trim($value ?? '') ?: null;
            $indikator->save();
            return response()->json([
                'success' => true,
                'value' => $indikator->kode_indikator ?? '',
                'display' => $indikator->kode_indikator ?? '—',
            ]);
        }

        if ($field === 'col_satuan' || strtolower($field) === 'satuan') {
            $indikator->satuan = trim($value ?? '') ?: null;
            $indikator->save();
            return response()->json([
                'success' => true,
                'value' => $indikator->satuan ?? '',
                'display' => $indikator->satuan ?? '',
            ]);
        }

        // DataIndikator values (nilai, sumber_data, or custom_fields)
        $data = DataIndikator::firstOrNew([
            'indikator_id' => $indikator->id,
            'tahun_id' => $tahunId,
        ]);

        if ($field === 'col_nilai' || strtolower($field) === 'nilai') {
            $valTrim = trim($value ?? '');
            if ($valTrim !== '') {
                $normalized = str_replace(',', '.', $valTrim);
                $data->nilai = $normalized;
            } else {
                $data->nilai = null;
            }
            $data->updated_by = auth()->id() ?? 1;
            $data->save();
            return response()->json([
                'success' => true,
                'value' => $data->nilai ?? '',
                'display' => $data->formatted_nilai,
            ]);
        }

        if ($field === 'col_sumber' || strtolower($field) === 'sumber data' || strtolower($field) === 'sumber_data') {
            $data->sumber_data = trim($value ?? '') ?: null;
            $data->updated_by = auth()->id() ?? 1;
            $data->save();
            return response()->json([
                'success' => true,
                'value' => $data->sumber_data ?? '',
                'display' => $data->sumber_data ?? '',
            ]);
        }

        // Custom fields by column ID
        $custom = $data->custom_fields ?? [];
        if (!is_array($custom)) {
            $custom = [];
        }
        $custom[$field] = $value;
        $data->custom_fields = $custom;
        $data->updated_by = auth()->id() ?? 1;
        $data->save();

        // If the edited column's name is a 4-digit year, also sync to that year's DataIndikator record
        if ($indikator->urusan) {
            foreach ($indikator->urusan->tables as $tConfig) {
                foreach ($tConfig['columns'] ?? [] as $tCol) {
                    if (($tCol['id'] ?? '') === $field && preg_match('/^(19|20)\d{2}$/', trim($tCol['name'] ?? ''), $mYear)) {
                        $yVal = (int) $mYear[0];
                        $tModel = Tahun::firstOrCreate(['tahun' => $yVal], ['status_aktif' => true, 'is_default' => false]);
                        $diYear = DataIndikator::firstOrNew([
                            'indikator_id' => $indikator->id,
                            'tahun_id' => $tModel->id,
                        ]);
                        $diYear->nilai = ($value !== null && trim($value) !== '') ? str_replace(',', '.', trim($value)) : null;
                        $diCustom = $diYear->custom_fields ?? [];
                        if (!is_array($diCustom)) $diCustom = [];
                        $diCustom[$field] = $value;
                        $diYear->custom_fields = $diCustom;
                        $diYear->updated_by = auth()->id() ?? 1;
                        $diYear->save();
                        break 2;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'value' => $value ?? '',
            'display' => $value ?? '',
        ]);
    }

    public function quickCreateRow(Request $request, $id)
    {
        $urusan = is_numeric($id) ? Urusan::findOrFail($id) : Urusan::where('slug', $id)->firstOrFail();
        $tableId = (int) $request->input('table_id', 1);

        $maxOrder = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($tableId) {
                if ($tableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $tableId);
                }
            })
            ->whereNull('parent_id')
            ->max('urutan') ?? 0;

        // Cek apakah tabel ini menggunakan pola baris-tahun (misal: 2023, 2024, 2025)
        $existingYears = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($tableId) {
                if ($tableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $tableId);
                }
            })
            ->whereNull('parent_id')
            ->pluck('nama_indikator')
            ->filter(fn($n) => preg_match('/^(19|20)\d{2}$/', trim($n)))
            ->map(fn($n) => (int)trim($n))
            ->toArray();

        $autoYearName = '';
        if (!empty($existingYears)) {
            $autoYearName = (string) (max($existingYears) + 1);
        }

        // Anti-spam: prevent creating duplicate rows in rapid succession (< 2 seconds)
        $recentRow = Indikator::where('urusan_id', $urusan->id)
            ->where('table_id', $tableId)
            ->whereNull('parent_id')
            ->where('nama_indikator', $autoYearName)
            ->where('created_at', '>=', now()->subSeconds(2))
            ->first();

        if ($recentRow) {
            return response()->json([
                'success' => true,
                'message' => 'Baris baru berhasil ditambahkan.',
                'indikator' => $recentRow,
            ]);
        }

        $newOrder = $maxOrder + 1;

        $indikator = Indikator::create([
            'urusan_id' => $urusan->id,
            'table_id' => $tableId,
            'parent_id' => null,
            'nama_indikator' => $autoYearName,
            'tipe_data' => 'numerik',
            'urutan' => $newOrder,
            'status_aktif' => true,
        ]);

        ActivityLog::record(
            'create_indikator',
            'Menambahkan baris ke-' . $newOrder . ' di urusan ' . $urusan->nama_urusan,
            $indikator
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Baris baru berhasil ditambahkan.',
                'indikator' => $indikator
            ]);
        }

        return back()->with('success', 'Baris baru berhasil ditambahkan.');
    }

    public function storeSubRows(Request $request, $id)
    {
        $parentIndikator = Indikator::findOrFail($id);
        $subNames = $request->input('sub_names');

        if (is_string($subNames)) {
            $subNames = array_map('trim', explode(',', $subNames));
        }
        if (!is_array($subNames)) {
            $subNames = [];
        }
        $subNames = array_values(array_filter($subNames, fn($n) => trim($n) !== ''));

        if (empty($subNames)) {
            $subNames = ['Sub-Baris Baru'];
        }

        $tableId = $parentIndikator->table_id ?? 1;
        $maxOrder = Indikator::where('parent_id', $parentIndikator->id)->max('urutan') ?? 0;

        $tConfig = $parentIndikator->urusan->getTableConfig($tableId);
        $uraianCol = collect($tConfig['columns'] ?? [])->first(function ($c) {
            $n = strtolower(trim($c['name'] ?? ''));
            return in_array($n, ['uraian', 'nama', 'indikator', 'nama indikator']);
        });
        $uraianColId = $uraianCol['id'] ?? null;

        // Default year for DataIndikator record
        $defaultTahun = Tahun::where('is_default', true)->first() ?: Tahun::first();

        $created = DB::transaction(function () use ($subNames, $maxOrder, $parentIndikator, $tableId, $uraianColId, $defaultTahun) {
            $createdList = [];
            foreach ($subNames as $idx => $name) {
                $newOrder = $maxOrder + $idx + 1;
                $child = Indikator::create([
                    'urusan_id' => $parentIndikator->urusan_id,
                    'table_id' => $tableId,
                    'parent_id' => $parentIndikator->id,
                    'nama_indikator' => trim($name),
                    'satuan' => $parentIndikator->satuan,
                    'tipe_data' => $parentIndikator->tipe_data ?: 'numerik',
                    'urutan' => $newOrder,
                    'status_aktif' => true,
                ]);

                // Pre-seed DataIndikator so the cell immediately displays the name if custom Uraian column exists
                if ($uraianColId && $defaultTahun) {
                    DataIndikator::create([
                        'indikator_id' => $child->id,
                        'tahun_id' => $defaultTahun->id,
                        'custom_fields' => [$uraianColId => trim($name)],
                        'updated_by' => auth()->id() ?? 1,
                    ]);
                }

                $createdList[] = $child;
            }
            return $createdList;
        });

        ActivityLog::record(
            'create_sub_indikator',
            "Menambahkan " . count($created) . " sub-baris pada '{$parentIndikator->nama_indikator}' di urusan {$parentIndikator->urusan->nama_urusan}",
            $parentIndikator
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($created) . ' sub-baris berhasil ditambahkan.',
                'sub_indikators' => $created,
            ]);
        }

        return back()->with('success', count($created) . ' sub-baris berhasil ditambahkan.');
    }
}
