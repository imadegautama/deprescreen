<?php

namespace App\Http\Controllers;

use App\Models\Symptom;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SymptomController extends Controller
{
    /**
     * Tampilkan daftar semua gejala (untuk admin/management)
     */
    public function index()
    {
        $symptoms = Symptom::paginate(15);

        return Inertia::render('Symptom/Index', [
            'symptoms' => $symptoms,
        ]);
    }

    /**
     * Tampilkan form untuk menambah gejala baru
     */
    public function create()
    {
        return Inertia::render('Symptom/Create', [
            'types' => ['scale', 'boolean'],
        ]);
    }

    /**
     * Simpan gejala baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:symptoms,code|max:10',
            'label' => 'required|string|max:255',
            'is_core' => 'required|boolean',
            'is_sensitive' => 'required|boolean',
            'type' => 'required|in:scale,boolean',
        ]);

        Symptom::create($validated);

        return redirect()->route('symptoms.index')
            ->with('success', 'Gejala berhasil ditambahkan');
    }

    /**
     * Tampilkan form edit gejala
     */
    public function edit(Symptom $symptom)
    {
        return Inertia::render('Symptom/Edit', [
            'symptom' => $symptom,
            'types' => ['scale', 'boolean'],
        ]);
    }

    /**
     * Update gejala
     */
    public function update(Request $request, Symptom $symptom)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:symptoms,code,' . $symptom->symptom_id . ',symptom_id|max:10',
            'label' => 'required|string|max:255',
            'is_core' => 'required|boolean',
            'is_sensitive' => 'required|boolean',
            'type' => 'required|in:scale,boolean',
        ]);

        $symptom->update($validated);

        return redirect()->route('symptoms.index')
            ->with('success', 'Gejala berhasil diperbarui');
    }

    /**
     * Hapus gejala
     */
    public function destroy(Symptom $symptom)
    {
        // Cek apakah ada jawaban yang menggunakan gejala ini
        $answerCount = $symptom->answers()->count();

        if ($answerCount > 0) {
            return redirect()->route('symptoms.index')
                ->with('error', 'Tidak dapat menghapus gejala yang sudah memiliki jawaban. Data: ' . $answerCount . ' jawaban');
        }

        $symptom->delete();

        return redirect()->route('symptoms.index')
            ->with('success', 'Gejala berhasil dihapus');
    }

    /**
     * Get gejala untuk API (misalnya untuk frontend select options)
     */
    public function getSymptoms()
    {
        $symptoms = Symptom::select('symptom_id', 'code', 'label', 'is_core', 'type')->get();

        return response()->json($symptoms);
    }
}
