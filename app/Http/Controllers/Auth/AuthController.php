<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password harus diisi.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->with('error', 'Akun Anda telah dinonaktifkan.');
            }

            ActivityLog::record('login', 'auth', "Login berhasil: {$user->email}");

            // Redirect berdasarkan role
            if ($user->hasRole(['superadmin', 'admin', 'bendahara', 'petugas', 'pimpinan'])) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('user.dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email atau password salah.');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'type' => 'required|in:masyarakat,opd,instansi_pemerintah,perusahaan,organisasi,internal,lainnya',
            'institution_name' => 'nullable|required_if:type,opd,instansi_pemerintah,perusahaan,organisasi|string|max:255',
            'nik' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'agree' => 'accepted',
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'password.required' => 'Password harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'type.required' => 'Jenis penyewa harus dipilih.',
            'address.required' => 'Alamat harus diisi.',
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole('penyewa');

        // Buat profil customer
        Customer::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'type' => $request->type,
            'nik' => $request->nik,
            'institution_name' => $request->institution_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'verification_status' => 'pending',
            'is_active' => true,
        ]);

        ActivityLog::record('register', 'auth', "Registrasi baru: {$user->email}");

        Auth::login($user);

        return redirect()->route('user.dashboard')
            ->with('success', 'Registrasi berhasil! Akun Anda sedang menunggu verifikasi.');
    }

    public function logout(Request $request)
    {
        ActivityLog::record('logout', 'auth', 'Logout: ' . auth()->user()->email);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
