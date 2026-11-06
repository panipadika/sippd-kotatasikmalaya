<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tahun;
use App\Models\ActivityLog;

class TahunController extends Controller
{
    public function index()
    {
        $tahuns = Tahun::withCount('dataIndikators')->orderByDesc('tahun')->get();
        return view('tahun.index', compact('tahuns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:1900|max:2100|unique:tahun,tahun',
            'keterangan' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ], [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.unique' => 'Tahun ini sudah terdaftar di sistem.',
            'tahun.integer' => 'Tahun harus berupa 4 digit angka.',
        ]);

        if (!empty($validated['is_default'])) {
            Tahun::where('is_default', true)->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = false;
        }

        $validated['status_aktif'] = true;

        $tahun = Tahun::create($validated);

        ActivityLog::record('create_tahun', 'Menambahkan tahun data baru: ' . $tahun->tahun, $tahun);

        return back()->with('success', 'Tahun data ' . $tahun->tahun . ' berhasil ditambahkan.');
    }

    public function setDefault($id)
    {
        $tahun = Tahun::findOrFail($id);

        Tahun::where('is_default', true)->update(['is_default' => false]);
        $tahun->is_default = true;
        $tahun->status_aktif = true;
        $tahun->save();

        session(['selected_tahun' => $tahun->tahun]);

        ActivityLog::record('set_default_tahun', 'Menjadikan tahun ' . $tahun->tahun . ' sebagai tahun default aktif', $tahun);

        return back()->with('success', 'Tahun ' . $tahun->tahun . ' berhasil dijadikan tahun aktif default.');
    }

    public function toggleStatus($id)
    {
        $tahun = Tahun::findOrFail($id);
        $tahun->status_aktif = !$tahun->status_aktif;
        $tahun->save();

        $status = $tahun->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::record('toggle_status_tahun', 'Tahun ' . $tahun->tahun . ' ' . $status, $tahun);

        return back()->with('success', 'Tahun ' . $tahun->tahun . ' berhasil ' . $status . '.');
    }

    public function destroy($id)
    {
        $tahun = Tahun::findOrFail($id);

        if ($tahun->dataIndikators()->count() > 0) {
            return back()->with('error', 'Tahun ' . $tahun->tahun . ' tidak dapat dihapus karena sudah memiliki ' . $tahun->dataIndikators()->count() . ' catatan data.');
        }

        $tahunNomor = $tahun->tahun;
        $tahun->delete();

        ActivityLog::record('delete_tahun', 'Menghapus tahun data: ' . $tahunNomor);

        return back()->with('success', 'Tahun ' . $tahunNomor . ' berhasil dihapus.');
    }
}
