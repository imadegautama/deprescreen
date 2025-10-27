<?php

namespace App\Http\Controllers;

use App\Models\Threshold;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThresholdController extends Controller
{
    /**
     * Tampilkan daftar threshold (kriteria scoring)
     */
    public function index()
    {
        $thresholds = Threshold::orderBy('min_score')->get();

        return Inertia::render('Threshold/Index', [
            'thresholds' => $thresholds,
        ]);
    }

    /**
     * Tampilkan form tambah threshold baru
     */
    public function create()
    {
        return Inertia::render('Threshold/Create');
    }

    /**
     * Simpan threshold baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'level' => 'required|string|in:rendah,sedang,tinggi|unique:thresholds,level',
            'min_score' => 'required|integer|min:0',
            'max_score' => 'required|integer|min:0|gt:min_score',
            'advice_text' => 'required|string|max:1000',
        ]);

        // Cek overlap dengan threshold lain
        $overlap = Threshold::where(function ($q) use ($validated) {
            $q->whereBetween('min_score', [$validated['min_score'], $validated['max_score']])
                ->orWhereBetween('max_score', [$validated['min_score'], $validated['max_score']]);
        })->exists();

        if ($overlap) {
            return back()->withErrors([
                'overlap' => 'Range score tumpang tindih dengan threshold yang sudah ada',
            ]);
        }

        Threshold::create($validated);

        return redirect()->route('thresholds.index')
            ->with('success', 'Threshold berhasil ditambahkan');
    }

    /**
     * Tampilkan form edit threshold
     */
    public function edit(Threshold $threshold)
    {
        return Inertia::render('Threshold/Edit', [
            'threshold' => $threshold,
        ]);
    }

    /**
     * Update threshold
     */
    public function update(Request $request, Threshold $threshold)
    {
        $validated = $request->validate([
            'level' => 'required|string|in:rendah,sedang,tinggi|unique:thresholds,level,' . $threshold->threshold_id . ',threshold_id',
            'min_score' => 'required|integer|min:0',
            'max_score' => 'required|integer|min:0|gt:min_score',
            'advice_text' => 'required|string|max:1000',
        ]);

        // Cek overlap dengan threshold lain (exclude current)
        $overlap = Threshold::where('threshold_id', '!=', $threshold->threshold_id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('min_score', [$validated['min_score'], $validated['max_score']])
                    ->orWhereBetween('max_score', [$validated['min_score'], $validated['max_score']]);
            })->exists();

        if ($overlap) {
            return back()->withErrors([
                'overlap' => 'Range score tumpang tindih dengan threshold yang sudah ada',
            ]);
        }

        $threshold->update($validated);

        return redirect()->route('thresholds.index')
            ->with('success', 'Threshold berhasil diperbarui');
    }

    /**
     * Hapus threshold
     */
    public function destroy(Threshold $threshold)
    {
        $threshold->delete();

        return redirect()->route('thresholds.index')
            ->with('success', 'Threshold berhasil dihapus');
    }

    /**
     * Get threshold untuk scoring (API endpoint)
     */
    public function getThresholds()
    {
        $thresholds = Threshold::select('threshold_id', 'level', 'min_score', 'max_score', 'advice_text')->get();

        return response()->json($thresholds);
    }
}
