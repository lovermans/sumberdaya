<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
    * Display the registration view.
    *
    * @return \Illuminate\View\View
    */
    public function create()
    {
        $data = DB::query()->select('sdm_nama','sdm_no_absen')->from('sdms')->whereNull('sdm_tgl_berhenti')->get();
        // dd($data);
        $HtmlPenuh = view('tambah-akun', compact('data'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        $HtmlHeader = ['Vary' => 'Accept'];
        return request()->pjax() ? response($HtmlIsi)->withHeaders($HtmlHeader) : $HtmlPenuh;
    }
    
    /**
    * Handle an incoming registration request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\RedirectResponse
    *
    * @throws \Illuminate\Validation\ValidationException
    */
    public function store(Request $request)
    {   
        $validasi = validator(
            request()->all(),
            [
                'foto_profil' => ['required', 'image']
            ]
        );
        $validasi->validate();
        $validasi->safe()->only('foto_profil')['foto_profil']->storeAs('sdm/foto-profil','foto-profil.webp');
        return back()->with('pesan', 'Foto profil telah tersimpan.');
    }
}
