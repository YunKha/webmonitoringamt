<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\DeliveryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * GET /api/tickets/available
     * Ambil semua tiket yang belum diambil (status = available)
     */
    public function available()
    {
        try {
            $tickets = Ticket::with('driver')
                ->where('status', 'available')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data tiket tersedia berhasil diambil',
                'data' => $tickets,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available tickets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tiket: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/tickets/my-jobs
     * Ambil tiket yang sedang dikerjakan oleh sopir yang login
     */
    public function myJobs(Request $request)
    {
        try {
            $tickets = Ticket::with(['deliveryPhotos', 'driver'])
                ->where('driver_id', $request->user()->id)
                ->where('status', 'in_progress')
                ->orderBy('taken_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data job saya berhasil diambil',
                'data' => $tickets,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting my jobs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data job: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/tickets/history
     * Ambil riwayat tiket yang sudah selesai oleh sopir yang login
     */
    public function history(Request $request)
    {
        try {
            $tickets = Ticket::with(['deliveryPhotos', 'driver'])
                ->where('driver_id', $request->user()->id)
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Data history berhasil diambil',
                'data' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/tickets/{id}
     * Ambil detail satu tiket beserta foto-fotonya
     */
    public function show($id)
    {
        try {
            // Eager load semua relasi yang dibutuhkan
            $ticket = Ticket::with(['deliveryPhotos', 'driver'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail tiket berhasil diambil',
                'data' => $ticket,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting ticket detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan',
            ], 404);
        }
    }

    /**
     * POST /api/tickets/{id}/take
     * Sopir mengambil tiket + upload foto check-in
     * 
     * Request body (multipart/form-data):
     * - nama_sopir (string)
     * - nomor_karnet (string) 
     * - latitude (numeric)
     * - longitude (numeric)
     * - photo (file)
     * - taken_at (datetime, optional)
     */
    public function take(Request $request, $id)
    {
        try {
            // Validasi input - sesuai dengan nama field dari Android
            $validated = $request->validate([
                'nama_sopir'    => 'required|string|max:255',
                'nomor_karnet'  => 'required|string|max:100',
                'photo'         => 'required|image|mimes:jpeg,jpg,png|max:5120', // max 5MB
                'latitude'      => 'required|numeric',
                'longitude'     => 'required|numeric',
                'taken_at'      => 'nullable|string', // Android mengirim sebagai string
            ]);

            // Cari tiket yang available
            $ticket = Ticket::where('status', 'available')->findOrFail($id);

            // Simpan foto check-in
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $path = $photo->store('delivery_photos', 'public');

                // Update status tiket
                $ticket->update([
                    'status'        => 'in_progress',
                    'driver_id'     => $request->user()->id,
                    'driver_name'   => $validated['nama_sopir'],
                    'karnet_number' => $validated['nomor_karnet'],
                    'taken_at'      => now(),
                ]);

                // Simpan foto ke tabel delivery_photos
                DeliveryPhoto::create([
                    'ticket_id'     => $ticket->id,
                    'user_id'       => $request->user()->id,
                    'photo_type'    => 'checkin',
                    'photo_path'    => $path,
                    'latitude'      => $validated['latitude'],
                    'longitude'     => $validated['longitude'],
                    'address'       => $request->address ?? null,
                    'photo_taken_at'=> now(),
                ]);

                // Reload data dengan relasi
                $ticket->load(['deliveryPhotos', 'driver']);

                return response()->json([
                    'success' => true,
                    'message' => 'Tiket berhasil diambil',
                    'data' => $ticket,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto wajib diupload',
                ], 422);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error taking ticket: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil tiket: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/complete
     * Sopir menyelesaikan pengantaran + upload foto check-out
     * 
     * Request body (multipart/form-data):
     * - latitude (numeric)
     * - longitude (numeric)
     * - photo (file)
     * - taken_at (datetime, optional)
     */
    public function complete(Request $request, $id)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'photo'     => 'required|image|mimes:jpeg,jpg,png|max:5120',
                'latitude'  => 'required|numeric',
                'longitude' => 'required|numeric',
                'taken_at'  => 'nullable|string',
            ]);

            // Cari tiket yang sedang dikerjakan oleh sopir ini
            $ticket = Ticket::where('driver_id', $request->user()->id)
                ->where('status', 'in_progress')
                ->findOrFail($id);

            // Simpan foto check-out
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $path = $photo->store('delivery_photos', 'public');

                // Update status tiket jadi completed
                $ticket->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                // Simpan foto checkout
                DeliveryPhoto::create([
                    'ticket_id'     => $ticket->id,
                    'user_id'       => $request->user()->id,
                    'photo_type'    => 'checkout',
                    'photo_path'    => $path,
                    'latitude'      => $validated['latitude'],
                    'longitude'     => $validated['longitude'],
                    'address'       => $request->address ?? null,
                    'photo_taken_at'=> now(),
                ]);

                // Reload data dengan relasi
                $ticket->load(['deliveryPhotos', 'driver']);

                return response()->json([
                    'success' => true,
                    'message' => 'Pengantaran berhasil diselesaikan',
                    'data' => $ticket,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto wajib diupload',
                ], 422);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error completing ticket: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan pengantaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}