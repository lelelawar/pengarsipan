<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->q, function ($query, $cari) {
                $query->where(function ($w) use ($cari) {
                    $w->where('nama', 'like', "%{$cari}%")
                      ->orWhere('username', 'like', "%{$cari}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('akun.index', compact('users'));
    }

    public function create()
    {
        return view('akun.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'role'     => ['required', Rule::in(['user', 'admin'])],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create($data); // password otomatis di-hash oleh cast di model

        ActivityLog::catat('tambah', "Menambah akun {$user->username} ({$user->role})");

        return redirect()->route('akun.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('akun.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'role'     => ['required', Rule::in(['user', 'admin'])],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Cegah admin menurunkan role dirinya sendiri (biar nggak kekunci)
        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->withInput()->with('error', 'Kamu tidak bisa menurunkan role akunmu sendiri.');
        }

        // Password kosong = tidak diganti
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        ActivityLog::catat('edit', "Mengubah akun {$user->username}");

        return redirect()->route('akun.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        // Akun yang sudah punya arsip atau riwayat aktivitas tidak boleh dihapus
        if ($user->arsips()->withTrashed()->exists() || $user->activityLogs()->exists()) {
            return back()->with('error', 'Akun ini sudah punya arsip atau riwayat aktivitas, jadi tidak bisa dihapus.');
        }

        $username = $user->username;
        $user->delete();

        ActivityLog::catat('hapus', "Menghapus akun {$username}");

        return redirect()->route('akun.index')->with('success', 'Akun berhasil dihapus.');
    }
}