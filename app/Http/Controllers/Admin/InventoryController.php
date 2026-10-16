<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Facility;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $query = Facility::with(['inventory', 'category'])
            ->where('is_active', true)
            ->whereIn('type', ['asset', 'equipment', 'consumable']);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $facilities = $query->orderBy('name')->paginate(10)->withQueryString();
        $categories = \App\Models\Category::active()->get();

        return view('admin.inventory.index', compact('facilities', 'categories'));
    }

    public function addStock(Request $request, Facility $facility)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->inventoryService->addStock($facility->id, $request->quantity, $request->notes);

        ActivityLog::record('update', 'inventory', "Menambah stok {$facility->name}: +{$request->quantity} {$facility->unit}");

        return response()->json(['success' => true, 'message' => "Berhasil menambah {$request->quantity} {$facility->unit} ke stok {$facility->name}."]);
    }

    public function adjust(Request $request, Facility $facility)
    {
        $request->validate([
            'status' => 'required|in:available,reserved,preparing,in_delivery,in_use,returned,inspection,laundry,damaged,lost,maintenance',
            'quantity' => 'required|integer|min:0',
            'notes' => 'required|string|max:500',
        ]);

        $this->inventoryService->adjustStock($facility->id, $request->status, $request->quantity, $request->notes);

        ActivityLog::record('adjust', 'inventory', "Penyesuaian stok {$facility->name}: status {$request->status} = {$request->quantity}");

        return response()->json(['success' => true, 'message' => 'Penyesuaian stok berhasil.']);
    }

    public function movements(Facility $facility, Request $request)
    {
        $query = InventoryMovement::where('facility_id', $facility->id)
            ->with('performedBy')
            ->latest();

        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }

        $movements = $query->paginate(50)->withQueryString();

        return view('admin.inventory.movements', compact('facility', 'movements'));
    }
}
