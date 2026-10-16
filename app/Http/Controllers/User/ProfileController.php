<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $customer = $user->customer;
        return view('user.profile.index', compact('user', 'customer'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'type' => 'required|in:masyarakat,opd,instansi_pemerintah,perusahaan,organisasi,internal,lainnya',
            'nik' => 'nullable|string|max:20',
            'nip' => 'nullable|string|max:20',
            'institution_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
        ]);

        $user->update(['name' => $request->name, 'phone' => $request->phone]);

        // Update atau buat customer profile
        Customer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $request->name,
                'type' => $request->type,
                'nik' => $request->nik,
                'nip' => $request->nip,
                'institution_name' => $request->institution_name,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'phone' => $request->phone,
                'email' => $user->email,
            ]
        );

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }
}
