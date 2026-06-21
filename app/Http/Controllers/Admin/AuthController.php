<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('login');
    }

    /**
     * Proses form login dan autentikasi pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginForm(Request $request)
    {
        $data = $request->only('email', 'password');
        if (Auth::attempt($data)) {
            $request->session()->regenerate();
            return redirect('/dashboard');
        } else {
            return redirect()->back()->with('gagal', 'Email atau Password anda salah');
        }
    }

    /**
     * Logout pengguna dan arahkan kembali ke halaman login.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}