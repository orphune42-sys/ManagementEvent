<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    // READ: Menampilkan semua sertifikat
    public function index()
    {
        $query = Certificate::with([
            'registration.user',
            'registration.event'
        ]);

        // User hanya melihat sertifikat miliknya
        if (auth()->user()->role !== 'admin') {
            $query->whereHas('registration', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $certificates = $query->get();

        return response()->json([
            'message' => 'Daftar sertifikat berhasil ditampilkan',
            'data' => $certificates
        ], 200);
    }

    // READ: Menampilkan detail sertifikat
    public function show($id)
    {
        $query = Certificate::with([
            'registration.user',
            'registration.event'
        ]);

        if (auth()->user()->role !== 'admin') {
            $query->whereHas('registration', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $certificate = $query->find($id);

        if (!$certificate) {
            return response()->json([
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail sertifikat berhasil ditampilkan',
            'data' => $certificate
        ], 200);
    }

    // CREATE: Menambahkan sertifikat
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_id' => [
                'required',
                'exists:registrations,registration_id',
                'unique:certificates,registration_id'
            ],
            'certificate_number' => 'required|string|max:255|unique:certificates,certificate_number',
            'issued_at' => 'nullable|date',
            'certificate_file' => 'nullable|file|mimes:pdf|max:5120',
            'status' => 'sometimes|string|max:255',
        ]);

        // Pastikan user hanya membuat sertifikat untuk registrasinya sendiri
        $registration = Registration::find($validated['registration_id']);

        if (
            auth()->user()->role !== 'admin' &&
            $registration->user_id != auth()->id()
        ) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses ke registrasi tersebut'
            ], 403);
        }

        // Upload file sertifikat
        if ($request->hasFile('certificate_file')) {
            $validated['file_path'] = $request->file('certificate_file')
                ->store('certificates', 'public');

            unset($validated['certificate_file']);
        }

        $certificate = Certificate::create($validated);

        return response()->json([
            'message' => 'Sertifikat berhasil ditambahkan',
            'data' => $certificate->load('registration.user', 'registration.event')
        ], 201);
    }

    // UPDATE: Mengubah data sertifikat
    public function update(Request $request, $id)
    {
        $query = Certificate::query();

        if (auth()->user()->role !== 'admin') {
            $query->whereHas('registration', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $certificate = $query->find($id);

        if (!$certificate) {
            return response()->json([
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'registration_id' => [
                'sometimes',
                'required',
                'exists:registrations,registration_id',
                Rule::unique('certificates', 'registration_id')
                    ->ignore($certificate->certificate_id, 'certificate_id'),
            ],
            'certificate_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('certificates', 'certificate_number')
                    ->ignore($certificate->certificate_id, 'certificate_id'),
            ],
            'issued_at' => 'sometimes|nullable|date',
            'certificate_file' => 'nullable|file|mimes:pdf|max:5120',
            'status' => 'sometimes|required|string|max:255',
        ]);

        // Jika registration_id diubah, periksa kepemilikannya
        if (isset($validated['registration_id'])) {
            $registration = Registration::find($validated['registration_id']);

            if (
                auth()->user()->role !== 'admin' &&
                $registration->user_id != auth()->id()
            ) {
                return response()->json([
                    'message' => 'Kamu tidak memiliki akses ke registrasi tersebut'
                ], 403);
            }
        }

        // Upload file sertifikat baru
        if ($request->hasFile('certificate_file')) {

            // Hapus file lama
            if ($certificate->file_path) {
                Storage::disk('public')->delete($certificate->file_path);
            }

            // Simpan file baru ke file_path
            $validated['file_path'] = $request->file('certificate_file')
                ->store('certificates', 'public');
        }

        // Hilangkan input upload dari data yang akan disimpan
        unset($validated['certificate_file']);

        $certificate->update($validated);

        return response()->json([
            'message' => 'Sertifikat berhasil diperbarui',
            'data' => $certificate->load('registration.user', 'registration.event')
        ], 200);
    }

    // DELETE: Menghapus sertifikat (admin saja)
    public function destroy($id)
    {
        $certificate = Certificate::find($id);

        if (!$certificate) {
            return response()->json([
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        // Hapus file sertifikat dari storage
        if ($certificate->file_path) {
            Storage::disk('public')->delete($certificate->file_path);
        }

        $certificate->delete();

        return response()->json([
            'message' => 'Sertifikat berhasil dihapus'
        ], 200);
    }
}
