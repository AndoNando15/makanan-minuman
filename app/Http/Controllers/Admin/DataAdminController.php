<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DataAdminController extends Controller
{
    // Tampilkan daftar pengguna (Baca)
    public function index()
    {
        $users = User::all();  // Ambil semua pengguna dari database
        return view('pages.data-admin.index', compact('users'));
    }

    // Tampilkan formulir untuk membuat pengguna baru (Buat)
    public function create()
    {
        return view('pages.data-admin.create');
    }

    // Simpan pengguna baru ke database (Simpan)
    public function store(Request $request)
    {
        // Validasi data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Buat pengguna baru
        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        // Arahkan ulang dengan pesan sukses
        return redirect()->route('data-admin.index')->with('success', 'User created successfully!');
    }

    // Tampilkan formulir untuk mengedit pengguna yang dipilih (Edit)
    public function edit($id)
    {
        $user = User::findOrFail($id);  // Temukan pengguna berdasarkan ID
        return view('pages.data-admin.edit', compact('user'));
    }

    // Perbarui pengguna yang ditentukan di database (Update)
    public function update(Request $request, $id)
    {
        // Validasi data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,  // Izinkan email saat ini
            'password' => 'nullable|string|min:6|confirmed',  // Field password opsional
        ]);

        $user = User::findOrFail($id);  // Temukan pengguna berdasarkan ID

        // Perbarui pengguna
        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $request->filled('password') ? bcrypt($validatedData['password']) : $user->password,  // Perbarui password jika ada
        ]);

        // Arahkan ulang dengan pesan sukses
        return redirect()->route('data-admin.index')->with('success', 'User updated successfully!');
    }

    // Hapus pengguna yang ditentukan (Hapus)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('data-admin.index')->with('success', 'User deleted successfully!');
    }
}