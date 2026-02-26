<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('lo_number',    'like', "%$search%")
                  ->orWhere('spbu_number','like', "%$search%")
                  ->orWhere('ship_to',    'like', "%$search%")
                  ->orWhere('driver_name','like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $drivers = User::drivers()->orderBy('name')->get(); // untuk dropdown sopir

        return view('admin.tickets.index', compact('tickets', 'drivers'));
    }

    public function create()
    {
        // Ambil daftar sopir untuk dropdown (opsional)
        $drivers = User::drivers()->orderBy('name')->get();
        return view('admin.tickets.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lo_number'      => 'required|string|unique:tickets,lo_number',
            'spbu_number'    => 'required|string',
            'ship_to'        => 'required|string',
            'quantity'       => 'required|numeric|min:0',
            'product_type'   => 'required|string',
            'distance_km'    => 'required|numeric|min:0',
            // Field baru - wajib diisi admin
            'driver_name'    => 'required|string|max:255',
            'karnet_number'  => 'required|string|max:100',
        ]);

        Ticket::create($validated + ['status' => 'available']);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Tiket berhasil dibuat');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load('photos');
        return view('admin.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $drivers = User::drivers()->orderBy('name')->get();
        return view('admin.tickets.edit', compact('ticket', 'drivers'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'lo_number'      => 'required|string|unique:tickets,lo_number,' . $ticket->id,
            'spbu_number'    => 'required|string',
            'ship_to'        => 'required|string',
            'quantity'       => 'required|numeric|min:0',
            'product_type'   => 'required|string',
            'distance_km'    => 'required|numeric|min:0',
            'driver_name'    => 'required|string|max:255',
            'karnet_number'  => 'required|string|max:100',
        ]);

        // Hanya boleh edit kalau masih available
        if ($ticket->status !== 'available') {
            return back()->with('error', 'Tiket yang sedang berjalan atau selesai tidak dapat diedit');
        }

        $ticket->update($validated);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Tiket berhasil diperbarui');
    }

    public function destroy(Ticket $ticket)
    {
        if ($ticket->status !== 'available') {
            return back()->with('error', 'Tiket yang sedang berjalan atau selesai tidak dapat dihapus');
        }

        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Tiket berhasil dihapus');
    }
}