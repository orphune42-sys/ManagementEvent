<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    // Menampilkan seluruh pengajuan atau pengajuan milik user
    public function index(Request $request)
    {
        $query = Certificate::with([
            'user',
            'registration.event'
        ]);

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json([
            'message' => 'Data sertifikat berhasil diambil.',
            'data' => $query->latest()->get()
        ]);
    }

    // Menampilkan detail pengajuan
    public function show(Request $request, $id)
    {
        $query = Certificate::with([
            'user',
            'registration.event'
        ]);

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        $certificate = $query->findOrFail($id);

        return response()->json([
            'message' => 'Detail sertifikat berhasil diambil.',
            'data' => $certificate
        ]);
    }

    // Membuat pengajuan sertifikat
    public function store(Request $request)
    {
        $isAdmin = $request->user()->role === 'admin';

        $rules = [
            'registration_id' => [
                'required',
                'exists:registrations,registration_id',
                'unique:certificates,registration_id'
            ],
            'recipient_name' => [
                'required',
                'string',
                'max:255'
            ],
        ];

        if ($isAdmin) {
            $rules['certificate_number'] = [
                'nullable',
                'string',
                'max:255',
                'unique:certificates,certificate_number'
            ];

            $rules['issued_at'] = [
                'nullable',
                'date'
            ];

            $rules['status'] = [
                'sometimes',
                'in:Menunggu,Terbit,Ditolak'
            ];
        }

        $validated = $request->validate($rules);

        $registration = Registration::findOrFail(
            $validated['registration_id']
        );

        // Registrasi yang dibatalkan tidak dapat digunakan untuk pengajuan sertifikat
        if ($registration->registration_status === 'Dibatalkan') {
            return response()->json([
                'message' => 'Anda tidak dapat mengajukan sertifikat karena registrasi telah dibatalkan.'
            ], 422);
        }

        // User hanya boleh mengajukan sertifikat dari registration miliknya
        if (!$isAdmin && $registration->user_id != $request->user()->id) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses ke pendaftaran tersebut.'
            ], 403);
        }

        // user_id diambil otomatis dari pemilik registration
        $data = [
            'user_id' => $registration->user_id,
            'registration_id' => $registration->registration_id,
            'recipient_name' => $validated['recipient_name'],
        ];

        if ($isAdmin) {
            $data['certificate_number'] =
                $validated['certificate_number'] ?? null;

            $data['issued_at'] = $validated['issued_at'] ?? null;
            $data['status'] = $validated['status'] ?? 'Menunggu';
        }

        $certificate = Certificate::create($data);

        return response()->json([
            'message' => 'Pengajuan sertifikat berhasil dibuat.',
            'data' => $certificate
        ], 201);
    }

    // Mengubah pengajuan sertifikat
    public function update(Request $request, $id)
    {
        $isAdmin = $request->user()->role === 'admin';

        $query = Certificate::query();

        if (!$isAdmin) {
            $query->where('user_id', $request->user()->id);
        }

        $certificate = $query->findOrFail($id);

        // User hanya bisa mengubah pengajuan yang masih menunggu
        if (!$isAdmin && $certificate->status !== 'Menunggu') {
            return response()->json([
                'message' => 'Pengajuan yang sudah diproses tidak dapat diubah.'
            ], 403);
        }

        if (!$isAdmin) {
            $validated = $request->validate([
                'recipient_name' => [
                    'required',
                    'string',
                    'max:255'
                ],
            ]);

            $certificate->update($validated);
        } else {
            $validated = $request->validate([
                'registration_id' => [
                    'sometimes',
                    'required',
                    'exists:registrations,registration_id',
                    Rule::unique('certificates', 'registration_id')
                        ->ignore($certificate->certificate_id, 'certificate_id')
                ],
                'recipient_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255'
                ],
                'certificate_number' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('certificates', 'certificate_number')
                        ->ignore($certificate->certificate_id, 'certificate_id')
                ],
                'issued_at' => [
                    'sometimes',
                    'nullable',
                    'date'
                ],
                'status' => [
                    'sometimes',
                    'in:Menunggu,Terbit,Ditolak'
                ],
            ]);

            // Jika registration diganti, periksa status dan ambil pemilik registration baru
            if (isset($validated['registration_id'])) {
                $registration = Registration::findOrFail(
                    $validated['registration_id']
                );

                // Registrasi yang dibatalkan tidak dapat digunakan untuk sertifikat
                if ($registration->registration_status === 'Dibatalkan') {
                    return response()->json([
                        'message' => 'Registrasi yang telah dibatalkan tidak dapat digunakan untuk sertifikat.'
                    ], 422);
                }

                $validated['user_id'] = $registration->user_id;
            }
            $certificate->update($validated);
        }

        return response()->json([
            'message' => 'Data sertifikat berhasil diperbarui.',
            'data' => $certificate->fresh()
        ]);
    }

    // Menghapus pengajuan sertifikat (admin saja)
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Hanya admin yang dapat menghapus sertifikat.'
            ], 403);
        }

        $certificate = Certificate::findOrFail($id);
        $certificate->delete();

        return response()->json([
            'message' => 'Data sertifikat berhasil dihapus.'
        ]);
    }
}
