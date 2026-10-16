<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Damage;
use App\Models\Facility;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class DamageController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $query = Damage::with(['facility', 'reportedBy', 'booking.customer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('damage_type', $request->type);
        }
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }

        $damages = $query->paginate(20)->withQueryString();
        $facilities = Facility::active()->orderBy('name')->get();

        return view('admin.damages.index', compact('damages', 'facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'quantity' => 'required|integer|min:1',
            'damage_type' => 'required|in:damage,loss',
            'description' => 'required|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'repair_cost' => 'nullable|numeric|min:0',
            'replacement_cost' => 'nullable|numeric|min:0',
            'reported_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('damages', 'public');
        }

        $damage = Damage::create([
            'facility_id' => $request->facility_id,
            'quantity' => $request->quantity,
            'damage_type' => $request->damage_type,
            'description' => $request->description,
            'photo' => $photoPath,
            'repair_cost' => $request->repair_cost ?? 0,
            'replacement_cost' => $request->replacement_cost ?? 0,
            'status' => 'reported',
            'reported_date' => $request->reported_date,
            'notes' => $request->notes,
            'reported_by' => auth()->id(),
        ]);

        // Update inventory
        $status = $request->damage_type === 'loss' ? 'lost' : 'damaged';
        $this->inventoryService->moveInventory(
            $request->facility_id, 'available', $status, $request->quantity,
            $request->damage_type, 'damage', $damage->id, $request->description
        );

        ActivityLog::record('create', 'damage', "Melaporkan kerusakan {$damage->damage_number}: {$request->quantity} unit {$damage->facility->name}");

        return response()->json(['success' => true, 'message' => 'Data kerusakan berhasil ditambahkan.', 'data' => $damage]);
    }

    public function update(Request $request, Damage $damage)
    {
        $request->validate([
            'status' => 'required|in:reported,in_repair,repaired,written_off',
            'repair_cost' => 'nullable|numeric|min:0',
            'repaired_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $damage->status;
        $damage->update($request->only(['status', 'repair_cost', 'repaired_date', 'notes']));

        // Jika selesai diperbaiki, kembalikan ke available
        if ($request->status === 'repaired' && $oldStatus !== 'repaired') {
            $this->inventoryService->moveInventory(
                $damage->facility_id, 'damaged', 'available', $damage->quantity,
                'maintenance_complete', 'damage', $damage->id, 'Selesai diperbaiki'
            );
        }

        ActivityLog::record('update', 'damage', "Update status kerusakan {$damage->damage_number} → {$request->status}");

        return response()->json(['success' => true, 'message' => 'Data kerusakan berhasil diperbarui.']);
    }

    public function destroy(Damage $damage)
    {
        ActivityLog::record('delete', 'damage', "Menghapus kerusakan {$damage->damage_number}");
        $damage->delete();

        return response()->json(['success' => true, 'message' => 'Data kerusakan berhasil dihapus.']);
    }
}
