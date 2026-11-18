<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller {
    public function index(){
        $registrations = Registration::with([
            'participant',
            'event'
        ])->get();

        return response()->json([
            'message' => 'Data registrasi peserta berhasil ditampilkan',
            'data' => $registrations
        ], 200);
    }

    public function show(string $id){
        $registration = Registration::with([
            'participant',
            'event'
        ])->find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Data registrasi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail registrasi peserta berhasil ditampilkan',
            'data' => $registration
        ], 200);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'participant_id' => 'required|exists:participants,participant_id',
            'id_event' => 'required|exists:events,id_event',
        ]);

        $registration = Registration::create([
            'participant_id' => $validated['participant_id'],
            'id_event' => $validated['id_event'],
            'registration_code' => 'REG-' . Str::upper(Str::random(10)),
            'registration_date' => now(),
            'registration_status' => 'Menunggu',
            'payment_status' => 'Belum dibayar',
            'total_payment' => 0,
        ]);

        return response()->json([
            'message' => 'Peserta berhasil didaftarkan ke event',
            'data' => $registration
        ], 201);
    }

    public function update(Request $request, string $id){
        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Data registrasi tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'registration_status' => 'sometimes|in:Menunggu,Terdaftar,Dibatalkan',
            'payment_status' => 'sometimes|in:Belum dibayar,Berhasil,Gratis',
            'total_payment' => 'sometimes|numeric|min:0',
        ]);

        $registration->update($validated);

        return response()->json([
            'message' => 'Data registrasi berhasil diperbarui',
            'data' => $registration
        ], 200);
    }

    public function destroy(string $id){
        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Data registrasi tidak ditemukan'
            ], 404);
        }

        $registration->update([
            'registration_status' => 'Dibatalkan'
        ]);

        return response()->json([
            'message' => 'Registrasi peserta berhasil dibatalkan'
        ], 200);
    }
}
