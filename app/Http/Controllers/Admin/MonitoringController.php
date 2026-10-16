<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function __construct(protected AvailabilityService $availabilityService) {}

    public function index()
    {
        $today = now()->toDateString();
        return view('admin.monitoring.index', compact('today'));
    }

    public function data(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        $data = $this->availabilityService->getMonitoringData($date);

        // Endpoint ini hanya melayani JSON untuk tabel AJAX monitoring.
        return response()->json($data);
    }
}
