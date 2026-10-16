<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityImage;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    public function __construct(protected AvailabilityService $availabilityService) {}

    public function index(Request $request)
    {
        $query = Facility::with(['category', 'inventory', 'primaryImage'])
            ->withCount('bookingItems');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $facilities = $query->orderBy('name')->paginate(10)->withQueryString();
        $categories = Category::active()->orderBy('sort_order')->get();

        return view('admin.facilities.index', compact('facilities', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        return view('admin.facilities.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|max:30|unique:facilities,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:building,asset,equipment,consumable',
            'unit' => 'required|string|max:30',
            'total_stock' => 'required|integer|min:1',
            'minimum_stock' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_hour,per_day,per_item,per_event',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:50',
            'building' => 'nullable|string|max:100',
            'facilities_list' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'is_bookable' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'min_booking_hours' => 'nullable|integer|min:1',
            'max_booking_days' => 'nullable|integer|min:1',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_bookable'] = $request->boolean('is_bookable', true);
        $validated['requires_approval'] = $request->boolean('requires_approval', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $facility = Facility::create($validated);

        ActivityLog::record('create', 'facility', "Membuat fasilitas: {$facility->name}", [
            'reference_type' => 'facility',
            'reference_id' => $facility->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Fasilitas berhasil ditambahkan.', 'data' => $facility]);
        }

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $fasilita)
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        $fasilita->load(['images', 'inventory']);

        if (request()->expectsJson()) {
            return response()->json($fasilita);
        }

        return view('admin.facilities.edit', ['facility' => $fasilita, 'categories' => $categories]);
    }

    public function update(Request $request, Facility $fasilita)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|max:30|unique:facilities,code,' . $fasilita->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:building,asset,equipment,consumable',
            'unit' => 'required|string|max:30',
            'total_stock' => 'required|integer|min:1',
            'minimum_stock' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_hour,per_day,per_item,per_event',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:50',
            'building' => 'nullable|string|max:100',
            'facilities_list' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'is_bookable' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'min_booking_hours' => 'nullable|integer|min:1',
            'max_booking_days' => 'nullable|integer|min:1',
        ]);

        $validated['is_bookable'] = $request->boolean('is_bookable', true);
        $validated['requires_approval'] = $request->boolean('requires_approval', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Update total_stock di inventory jika berubah
        $oldTotal = $fasilita->total_stock;
        $newTotal = $validated['total_stock'];

        $fasilita->update($validated);

        if ($oldTotal !== $newTotal) {
            $inventory = $fasilita->inventory;
            if ($inventory) {
                $diff = $newTotal - $oldTotal;
                $inventory->qty_total = $newTotal;
                $inventory->recalculate();
            }
        }

        ActivityLog::record('update', 'facility', "Memperbarui fasilitas: {$fasilita->name}", [
            'reference_type' => 'facility',
            'reference_id' => $fasilita->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Fasilitas berhasil diperbarui.', 'data' => $fasilita]);
        }

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $fasilita)
    {
        ActivityLog::record('delete', 'facility', "Menghapus fasilitas: {$fasilita->name}");
        $fasilita->delete();

        return response()->json(['success' => true, 'message' => 'Fasilitas berhasil dihapus.']);
    }

    public function uploadImage(Request $request, int $id)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $facility = Facility::findOrFail($id);
        $uploaded = [];

        foreach ($request->file('images') as $file) {
            $path = $file->store("facilities/{$id}", 'public');
            $isPrimary = $facility->images()->count() === 0;

            $image = FacilityImage::create([
                'facility_id' => $id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'is_primary' => $isPrimary,
                'sort_order' => $facility->images()->max('sort_order') + 1,
            ]);

            $uploaded[] = ['id' => $image->id, 'url' => $image->url, 'is_primary' => $image->is_primary];
        }

        return response()->json(['success' => true, 'images' => $uploaded]);
    }

    public function deleteImage(int $id)
    {
        $image = FacilityImage::findOrFail($id);
        $wasPrimary = $image->is_primary;
        $facilityId = $image->facility_id;

        $image->delete();

        // Set primary ke gambar pertama jika yg dihapus adalah primary
        if ($wasPrimary) {
            FacilityImage::where('facility_id', $facilityId)->oldest()->first()?->update(['is_primary' => true]);
        }

        return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus.']);
    }

    public function setPrimaryImage(int $id)
    {
        $image = FacilityImage::findOrFail($id);
        FacilityImage::where('facility_id', $image->facility_id)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json(['success' => true, 'message' => 'Gambar utama berhasil diubah.']);
    }

    public function inventoryDetail(int $id)
    {
        $facility = Facility::with('inventory')->findOrFail($id);
        $movements = InventoryMovement::with('performedBy')
            ->where('facility_id', $id)
            ->latest()
            ->paginate(15);
        $today = now()->toDateString();
        $availabilityDetail = $this->availabilityService->getAvailabilityDetail($id, $today);

        return view('admin.facilities.inventory', compact('facility', 'movements', 'availabilityDetail'));
    }
}
