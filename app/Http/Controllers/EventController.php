<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('creator')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar event berhasil ditampilkan',
            'data' => $events
        ], 200);
    }

    public function show($id)
    {
        $event = Event::with('creator')->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail event berhasil ditampilkan',
            'data' => $event
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at'    => 'required|date',
            'end_at'      => 'required|date|after_or_equal:start_at',
            'location'    => 'required|string|max:255',
            'quota'       => 'required|integer|min:1',
            'status'      => 'required|string|max:20',
        ]);

        $validated['created_by'] = Auth::id();

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil ditambahkan',
            'data' => $event
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan'
            ], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'admin' && $event->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengubah event ini'
            ], 403);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_at'    => 'sometimes|required|date',
            'end_at'      => 'sometimes|required|date|after_or_equal:start_at',
            'location'    => 'sometimes|required|string|max:255',
            'quota'       => 'sometimes|required|integer|min:1',
            'status'      => 'sometimes|required|string|max:20',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil diperbarui',
            'data' => $event
        ], 200);
    }

    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dihapus'
        ], 200);
    }
}
