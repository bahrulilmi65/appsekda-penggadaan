<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Facility;
use App\Models\Invoice;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicController extends Controller
{
    public function __construct(protected AvailabilityService $availabilityService) {}

    public function home()
    {
        $categories = Category::active()->withCount('facilities')->orderBy('sort_order')->get();
        $featuredFacilities = Facility::with(['category', 'primaryImage', 'inventory'])
            ->active()->bookable()->take(8)->get();
        return view('public.home', compact('categories', 'featuredFacilities'));
    }

    public function catalog(Request $request)
    {
        $query = Facility::with(['category', 'primaryImage', 'inventory'])
            ->active()->bookable();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $facilities = $query->paginate(12)->withQueryString();
        $categories = Category::active()->orderBy('sort_order')->get();

        return view('public.catalog', compact('facilities', 'categories'));
    }

    public function facilityDetail(string $slug)
    {
        $facility = Facility::with(['category', 'images', 'inventory'])
            ->where('slug', $slug)->firstOrFail();

        $relatedFacilities = Facility::with(['primaryImage'])
            ->where('category_id', $facility->category_id)
            ->where('id', '!=', $facility->id)
            ->active()->take(4)->get();

        return view('public.facility-detail', compact('facility', 'relatedFacilities'));
    }

    public function checkAvailability(Request $request)
    {
        $facilities = null;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($startDate && $endDate) {
            $all = $this->availabilityService->getAvailableFacilities($startDate, $endDate);

            if ($request->filled('category')) {
                $all = $all->where('category_id', (int) $request->category)->values();
            }

            $page = max(1, (int) $request->get('page', 1));
            $perPage = 12;
            $facilities = new LengthAwarePaginator(
                $all->forPage($page, $perPage)->values(),
                $all->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        $categories = Category::active()->orderBy('sort_order')->get();

        return view('public.check-availability', compact('facilities', 'startDate', 'endDate', 'categories'));
    }

    public function apiAvailability(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'nullable|integer|min:1',
            'exclude_booking_id' => 'nullable|integer',
        ]);

        $facility = Facility::with('inventory')->find($request->facility_id);
        $qty = (int) ($request->quantity ?? 1);

        if ($facility->isBuilding()) {
            $available = $this->availabilityService->checkBuildingAvailability(
                $facility->id,
                $request->start_date,
                $request->end_date,
                $request->start_time,
                $request->end_time,
                $request->exclude_booking_id
            );

            return response()->json([
                'available' => $available,
                'message' => $available
                    ? 'Fasilitas tersedia pada tanggal tersebut.'
                    : 'Fasilitas tidak tersedia. Sudah ada pemesanan pada tanggal tersebut.',
            ]);
        }

        $availableQty = $this->availabilityService->getAvailableQuantity(
            $facility->id,
            $request->start_date,
            $request->end_date,
            $request->exclude_booking_id
        );

        $isAvailable = $availableQty >= $qty;

        return response()->json([
            'available' => $isAvailable,
            'available_qty' => $availableQty,
            'requested_qty' => $qty,
            'total_stock' => $facility->inventory->qty_total ?? 0,
            'message' => $isAvailable
                ? "Stok tersedia: {$availableQty} {$facility->unit}."
                : "Stok tidak mencukupi. Tersedia: {$availableQty} {$facility->unit}, diminta: {$qty} {$facility->unit}.",
        ]);
    }

    public function facilityCalendar(int $id, Request $request)
    {
        $year = (int) ($request->year ?? date('Y'));
        $month = (int) ($request->month ?? date('m'));

        $calendar = $this->availabilityService->getBuildingCalendar($id, $year, $month);

        return response()->json($calendar);
    }

    public function verifyInvoice(string $token)
    {
        $invoice = Invoice::with(['booking.customer', 'booking.items.facility'])
            ->where('verification_token', $token)->firstOrFail();

        return view('public.invoice-verify', compact('invoice'));
    }
}
