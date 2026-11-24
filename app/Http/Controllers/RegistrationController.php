<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller {

    // R: Menampilkan daftar registrasi peserta, user melihat miliknya
     public function index(){
        $user = auth()->user();

        $query = Registration::with(['user', 'event']);

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'message' => 'Data registrasi peserta berhasil ditampilkan',
            'data' => $query->get()
        ], 200);
    }

    // R: Menampilkan detail registrasi
    public function show(string $id){
        $user = auth()->user();

        $registration = Registration::with(['user', 'event'])->find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Data registrasi tidak ditemukan'
            ], 404);
        }

        if ($user->role !== 'admin' && $registration->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses'], 403);
        }

        return response()->json([
            'message' => 'Detail registrasi peserta berhasil ditampilkan',
            'data' => $registration
        ], 200);
    }

    // C: Tambah registrasi peserta
    public function store(Request $request){
        $request->validate([
            'id_event' => 'required|exists:events,id_event',
        ]);

        $userId = auth()->id();

        //user tidak mendaftar event yang sama
        $alreadyRegistered = Registration::where('user_id', $userId)
            ->where('id_event', $request->id_event)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'message' => 'Pengguna sudah terdaftar pada event ini'
            ], 422);
        }

        $registration = Registration::create([
            'user_id' => $userId,
            'id_event' => $request->id_event,
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

    // U: Update data registrasi 
    public function update(Request $request, string $id){
        $user = auth()->user();
        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Data registrasi tidak ditemukan'
            ], 404);
        }

        //user: membatalkan pendaftaran
        if ($user->role !== 'admin') {
            if ($registration->user_id !== $user->id) {
                return response()->json(['message' => 'Anda tidak memiliki akses'], 403);
            }

            $registration->update(['registration_status' => 'Dibatalkan']);

            return response()->json([
                'message' => 'Pendaftaran berhasil dibatalkan oleh peserta',
                'data' => $registration
            ], 200);
        }
        //admin: memverifikasi status registrasi dan pembayaran
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

    // D: Hapus data registrasi (status menjadi Dibatalkan)
    public function destroy(string $id){
        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Data registrasi tidak ditemukan'], 404);
        }

        $registration->update(['registration_status' => 'Dibatalkan']);

        return response()->json([
            'message' => 'Registrasi peserta berhasil dibatalkan'
        ], 200);
    }
}