<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // READ: Menampilkan semua tiket
    public function index()
    {
        $tickets = Ticket::with('event')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tiket berhasil ditampilkan',
            'data' => $tickets
        ], 200);
    }

    // READ: Menampilkan detail tiket
    public function show($id)
    {
        $ticket = Ticket::with('event')->find($id);

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

        $ticket = Ticket::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil ditambahkan',
            'data' => $ticket
        ], 201);
    }

    // UPDATE: Mengubah data tiket
    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
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
