<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Facility;
use App\Models\Laundry;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class LaundryController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $query = Laundry::with(['facility', 'handledBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }

        $laundries = $query->paginate(20)->withQueryString();
        $facilities = Facility::active()->whereIn('type', ['asset', 'consumable'])->orderBy('name')->get();

        return view('admin.laundry.index', compact('laundries', 'facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'quantity' => 'required|integer|min:1',
            'date_in' => 'required|date',
            'estimated_done' => 'nullable|date|after_or_equal:date_in',
            'laundry_vendor' => 'nullable|string|max:100',
            'laundry_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $laundry = Laundry::create([
            'facility_id' => $request->facility_id,
            'quantity' => $request->quantity,
            'date_in' => $request->date_in,
            'estimated_done' => $request->estimated_done,
            'laundry_vendor' => $request->laundry_vendor,
            'laundry_cost' => $request->laundry_cost ?? 0,
            'notes' => $request->notes,
            'status' => 'in_laundry',
            'handled_by' => auth()->id(),
        ]);

        // Jika dari manual input (bukan dari return), kurangi available
        $facility = Facility::find($request->facility_id);
        $this->inventoryService->moveInventory(
            $request->facility_id, 'available', 'laundry', $request->quantity,
            'laundry', 'laundry', $laundry->id, 'Manual laundry entry'
        );

        ActivityLog::record('create', 'laundry', "Membuat laundry {$laundry->laundry_number}: {$request->quantity} {$facility->unit}");

        return response()->json(['success' => true, 'message' => 'Data laundry berhasil ditambahkan.', 'data' => $laundry]);
    }

    public function update(Request $request, Laundry $laundry)
    {
        $request->validate([
            'estimated_done' => 'nullable|date',
            'laundry_vendor' => 'nullable|string|max:100',
            'laundry_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'status' => 'required|in:pending,in_laundry,done,returned',
        ]);

        $laundry->update($request->only(['estimated_done', 'laundry_vendor', 'laundry_cost', 'notes', 'status']));

        return response()->json(['success' => true, 'message' => 'Data laundry berhasil diperbarui.']);
    }

    public function complete(Request $request, Laundry $laundry)
    {
        if ($laundry->status === 'returned') {
            return response()->json(['success' => false, 'message' => 'Laundry sudah selesai.'], 422);
        }

        $laundry->update([
            'status' => 'returned',
            'date_done' => now()->toDateString(),
        ]);

        // Kembalikan ke available
        $this->inventoryService->completeLaundry($laundry->facility_id, $laundry->quantity, $laundry->id);

        ActivityLog::record('complete', 'laundry', "Laundry selesai: {$laundry->laundry_number}, {$laundry->quantity} {$laundry->facility->unit} dikembalikan ke stok");

        return response()->json(['success' => true, 'message' => 'Laundry selesai. Stok berhasil dikembalikan.']);
    }

    public function destroy(Laundry $laundry)
    {
        ActivityLog::record('delete', 'laundry', "Menghapus laundry {$laundry->laundry_number}");
        $laundry->delete();

        return response()->json(['success' => true, 'message' => 'Data laundry berhasil dihapus.']);
    }
}
