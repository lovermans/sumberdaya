<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Pengelola extends Controller
{
    public function mulai()
    {
        if (auth()->user()) {
            $sandiKtp = Hash::check(auth()->user()->sdm_no_ktp, auth()->user()->password);
            $sandiBawaan = Hash::check('penggunaportalsdm', auth()->user()->password);
            if ($sandiKtp || $sandiBawaan) {
                session(['spanduk' => 'Sandi Anda kurang aman.']);
            }
        }

        return view('mulai');
    }

    public function tentangAplikasi()
    {
        $HtmlPenuh = view('tentang-aplikasi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        $HtmlHeader = ['Vary' => 'Accept'];
        return request()->pjax() ? response($HtmlIsi)->withHeaders($HtmlHeader) : $HtmlPenuh;
    }

    public function akun()
    {
        $HtmlPenuh = view('akun');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        $HtmlHeader = ['Vary' => 'Accept'];
        return request()->pjax() ? response($HtmlIsi)->withHeaders($HtmlHeader) : $HtmlPenuh;
    }

    public function ubahSandi()
    {
        if (request()->isMethod('post')) {
            $validasiSandi = validator(
                request()->all(),
                [
                    'password_lama' => ['required', 'string', 'current_password'],
                    'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
                ]
            );
            $validasiSandi->validate();
            $sandiBaru = Hash::make($validasiSandi->safe()->only('password')['password']);
            DB::transaction(function () use ($sandiBaru) {
                DB::table('sdms')->where('id',auth()->user()->id)->update(['password' => $sandiBaru]);
            });
            session()->forget('spanduk');
            cache()->flush();
            return back()->with('pesan', 'Sandi berhasil diubah.');
        }
        $HtmlPenuh = view('ubah-sandi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        $HtmlHeader = ['Vary' => 'Accept'];
        return request()->pjax() ? response($HtmlIsi)->withHeaders($HtmlHeader) : $HtmlPenuh;
    }
}
