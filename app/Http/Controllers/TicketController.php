<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // READ: Menampilkan semua tiket beserta event dan pembuat tiket
    public function index()
    {
        $tickets = Ticket::with(['event', 'user:id,name,email,role'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tiket berhasil ditampilkan',
            'data' => $tickets
        ], 200);
    }

    // READ: Menampilkan detail tiket
    public function show($id)
    {
        $ticket = Ticket::with(['event', 'user:id,name,email,role'])->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail tiket berhasil ditampilkan',
            'data' => $ticket
        ], 200);
    }

    // CREATE: Menambahkan tiket
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_event' => 'required|exists:events,id_event',
            'nama_tiket' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'kuota_tiket' => 'required|integer|min:1',
        ]);

        // Otomatis mengisi user_id dari akun yang sedang login
        $validated['user_id'] = auth('api')->id();

        $ticket = Ticket::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil ditambahkan',
            'data' => $ticket
        ], 201);
    }

    // UPDATE: Mengubah data tiket (Plus aturan Ownership Poin 2.4)
    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }

        $user = auth('api')->user();

        // Cek Ownership: jika bukan admin dan bukan pembuat tiket, tolak akses
        if ($user->role !== 'admin' && $ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya diizinkan mengubah data tiket yang Anda buat sendiri'
            ], 403);
        }

        $validated = $request->validate([
            'id_event' => 'sometimes|required|exists:events,id_event',
            'nama_tiket' => 'sometimes|required|string|max:255',
            'harga' => 'sometimes|required|numeric|min:0',
            'kuota_tiket' => 'sometimes|required|integer|min:1',
        ]);

        $ticket->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil diperbarui',
            'data' => $ticket
        ], 200);
    }

    // DELETE: Menghapus data tiket
    public function destroy($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dihapus'
        ], 200);
    }
}