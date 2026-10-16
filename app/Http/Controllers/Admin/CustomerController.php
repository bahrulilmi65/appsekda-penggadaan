<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['user'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('institution_name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->paginate(10)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:masyarakat,opd,instansi_pemerintah,perusahaan,organisasi,internal,lainnya',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'institution_name' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:20',
            'nip' => 'nullable|string|max:20',
        ]);

        $customer = Customer::create($request->only([
            'name', 'type', 'phone', 'email', 'address', 'city', 'province',
            'institution_name', 'nik', 'nip', 'npwp', 'notes',
        ]));

        ActivityLog::record('create', 'customer', "Membuat penyewa: {$customer->name}");

        return response()->json(['success' => true, 'message' => 'Penyewa berhasil ditambahkan.', 'data' => $customer]);
    }

    public function edit(Customer $penyewa)
    {
        return response()->json($penyewa);
    }

    public function update(Request $request, Customer $penyewa)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:masyarakat,opd,instansi_pemerintah,perusahaan,organisasi,internal,lainnya',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        $penyewa->update($request->only([
            'name', 'type', 'phone', 'email', 'address', 'city', 'province',
            'institution_name', 'nik', 'nip', 'npwp', 'notes', 'is_active',
        ]));

        ActivityLog::record('update', 'customer', "Memperbarui penyewa: {$penyewa->name}");

        return response()->json(['success' => true, 'message' => 'Data penyewa berhasil diperbarui.']);
    }

    public function destroy(Customer $penyewa)
    {
        ActivityLog::record('delete', 'customer', "Menghapus penyewa: {$penyewa->name}");
        $penyewa->delete();

        return response()->json(['success' => true, 'message' => 'Data penyewa berhasil dihapus.']);
    }

    public function verify(Request $request, Customer $customer)
    {
        $request->validate([
            'action' => 'required|in:verify,reject',
            'reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        if ($request->action === 'verify') {
            $customer->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'rejection_reason' => null,
            ]);
            $msg = 'Penyewa berhasil diverifikasi.';
        } else {
            $customer->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $request->reason,
            ]);
            $msg = 'Verifikasi penyewa ditolak.';
        }

        ActivityLog::record('verify', 'customer', "Verifikasi penyewa {$customer->name}: {$request->action}");

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function detail(Customer $customer)
    {
        $customer->load(['user', 'documents', 'bookings' => fn($q) => $q->latest()->take(10)]);
        return response()->json($customer);
    }
}
