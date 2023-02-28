@extends('rangka') 
@section('isi')
<div id="pemberitahuan" class="tcetak">
    <div class="spanduk tcetak">
        <p><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ asset(mix('/ikon.svg')) . '#informasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg> Sandi Anda kurang aman.</p><a class="isi-xhr sekunder" href="">AMANKAN</a>
    </div>
    <div class="pesan tcetak">
        <p>Selamat Anda mendapat pesan kilat. <a href="/">Tautan</a></p><button class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ asset(mix('/ikon.svg')) . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg></button>
    </div>
    <div class="periksa tcetak">
        <details>
            <summary>Periksa kesalahan :</summary>
            <ul>
                <li>Kesalahan 1.</li>
                <li>Kesalahan 2.</li>
            </ul>
        </details>
        <button class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ asset(mix('/ikon.svg')) . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></button>
    </div>
</div>
<h2>Sejarah {{ str(1)->padLeft(5,'0') }}</h2>
<div class="kartu">
    <p><b><u>PT. Kepuh Kencana Arum</u></b> berdiri pada tahun 1991 di kota Mojokerto, Jawa Timur. Pada awal mula
        berdiri bergerak di bidang industri atap gelombang tanpa sambungan. Seiring dengan perkembangan waktu menambah
        ragam produk berupa genteng metal, kuda-kuda baja ringan dan rangka plafon yang seluruhnya menggunakan bahan
        baja lapis <i>zinc</i> dan aluminium.</p>
    <p class="kartu">Dengan kerja keras seluruh lapisan personil telah berhasil mengembangkan perusahaan dengan
        mendirikan pabrik-pabrik dan kantor cabang yang tersebar di Nusantara. Bahan baku yang dipilih pun menggunakan
        bahan baku yang berstandar SNI. Sehingga dengan itu dapat memberikan pelayanan terbaik bagi semua pelanggan,
        mendukung pembangunan Nasional dan tumbuh lebih luas di masa depan.</p>
    <div class="cari-data">
        <details>
            <summary>Tampilkan pencarian :</summary>
            <form class="post-xhr kartu" method="GET" action="{{ url('/testing') }}"> @csrf
                <div class="isian">
                    <label for="idAbsen">ID Absen</label> <input id="idAbsen" type="text" name="id_absen"
                        value="{{ old('id_absen', auth()->id_absen ?? null) }}" pattern="^[0-9]{8}$" inputmode="numeric"
                        required> <span class="t-bantu">8 digit ID absen</span>
                </div>
                <div class="isian"> <label for="password">Sandi</label> <input id="password" type="password"
                        name="password" required> <span class="t-bantu">Sandi akun</span> </div>
                <div class="isian"> <label for="tglMasuk">Tanggal Masuk</label> <input id="tglMasuk" type="date"
                        name="tgl_masuk" value="{{ old('tgl_masuk') }}" required> <span class="t-bantu">Isi tanggal
                        masuk</span> </div>
                <div class="isian gspan-4"> <label for="ket">Keterangan</label>
                    <textarea id="ket" name="keterangan" required cols="3"></textarea> <span
                        class="t-bantu">Keterangan</span>
                </div>
                <div class="isian"> <label for="jenis">Negara</label>
                    <select name="negara" id="jenis" class="pil" required>
                        <option value=""></option>
                        <option value="VALUE 2">VALUE 2</option>
                        <optgroup label="LABEL 1">
                            <option value="VALUE 1">VALUE 1</option>
                            <option value="VALUE 2">VALUE 2</option>
                            <option value="VALUE 3">VALUE 3</option>
                        </optgroup>
                        <optgroup label="LABEL 2">
                            <option value="VALUE 21">VALUE 1</option>
                            <option value="VALUE 22">VALUE 2</option>
                            <option value="VALUE 23">VALUE 3</option>
                        </optgroup>
                    </select>
                    <span class="t-bantu">Pilih satu</span>
                </div>
                <div class="isian"> <label for="jenis2">Negara</label> <select name="negara2[]" id="jenis2"
                        class="pil" multiple required>
                        <option value=""></option>
                        <option value="VALUE 2">VALUE 2</option>
                        <option value="VALUE 3">VALUE 3</option>
                        <option value="VALUE 21">VALUE 1</option>
                        <option value="VALUE 22">VALUE 2</option>
                        <option value="VALUE 23">VALUE 3</option>
                    </select> <span class="t-bantu">Pilih satu atau lebih</span> </div> <button class="sekunder"
                    type="submit">MASUK</button>
            </form>
        </details>
    </div>
    <div class="trek-data">
        <span class="ket">1 - 25 dari 10.000</span>
        <span class="bph">
            <select name="bph" id="data-bph" class="pil-saja">
                <option>25</option>
                <option>50</option>
                <option>75</option>
                <option>100</option>
            </select>
        </span>
        <span class="ket">baris/halaman.</span>
        <span class="trek">
            <a class="isi-xhr" href="/"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#awal' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></a>
            <a class="isi-xhr" href="/"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#mundur' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></a>
            <a class="isi-xhr" href="/"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#maju' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></a>
            <a class="isi-xhr" href="/"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#akhir' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></a>
        </span>
    </div>
    <div class="data">
        <table class="tabel" id="browser">
            <thead>
                <tr>
                    <th></th>
                    <th>Browser</th>
                    <th>Sessions</th>
                    <th>Percentage</td>
                    <th>New Users</th>
                    <th>Avg. Duration</th>
                    <th>Keterangan 1</th>
                    <th>Keterangan 2</th>
                    <th>Keterangan 3</th>
                    <th>Keterangan 4</th>
                    <th>Keterangan 5</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Chrome</td>
                    <td>9,562</td>
                    <td>68.81%</td>
                    <td>7,895</td>
                    <td>01:07</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Firefox</td>
                    <td>2,403</td>
                    <td>17.29%</td>
                    <td>2,046</td>
                    <td>00:59</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Safari</td>
                    <td>1,089</td>
                    <td>2.63%</td>
                    <td>904</td>
                    <td>00:59</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Internet Explorer</td>
                    <td>366</td>
                    <td>2.63%</td>
                    <td>333</td>
                    <td>01:01</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Safari (in-app)</td>
                    <td>162</td>
                    <td>1.17%</td>
                    <td>112</td>
                    <td>00:58</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Opera</td>
                    <td>103</td>
                    <td>0.74%</td>
                    <td>87</td>
                    <td>01:22</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Edge</td>
                    <td>98</td>
                    <td>0.71%</td>
                    <td>69</td>
                    <td>01:18</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg></button>
                <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                        href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <div class="pil-aksi"><button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg></button>
                        <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                                href="{{ route('login') }}">Lihat</a> </div></div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
                <tr>
                    <th> <button><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menuvert' }}"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg></button>
                        <div class="aksi"> <a class="isi-xhr" href="{{ route('login') }}">Ubah</a> <a class="isi-xhr"
                                href="{{ route('login') }}">Lihat</a> </div>
                    </th>
                    <td>Other</td>
                    <td>275</td>
                    <td>6.02%</td>
                    <td>90</td>
                    <td>N/A</td>
                    <td>Keterangan 1</td>
                    <td>Keterangan 2</td>
                    <td>Keterangan 3</td>
                    <td>Keterangan 4</td>
                    <td>Keterangan 5</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<p><b><u>Informasi</u></b> : Aplikasi ini hanya menyajikan fitur informasi data SDM. Aplikasi ini tidak menyajikan fitur
    perhitungan kehadiran maupun gaji dan kompensasi lainnya.</p>
<div class="pintasan tcetak">
    <a class="isi-xhr" href="{{ route('login') }}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#tambah' }}"
            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
    </svg></a>
    <a class="isi-xhr" href="{{ route('login') }}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#unduh' }}"
            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
    </svg></a>
    <button class="isi-xhr" data-href="{{ route('login') }}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#unggah' }}"
            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
    </svg></button>
    <a class="isi-xhr" href="{{ route('login') }}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#unggah' }}"
            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
    </svg></a>
</div>
<div>{{ filemtime(resource_path('css/css.css')) }}</div>
<script>
    var pilihC = document.querySelectorAll(".pil-cari"),
        pilihT = document.querySelectorAll(".pil-tambah"),
        pilihS = document.querySelectorAll(".pil-saja"),
        pilih = document.querySelectorAll(".pil");
    pilihT.forEach(function (a) {
        new SlimSelect({
            select: a,
            searchPlaceholder: "CARI",
            searchText: "KOSONG",
            searchingText: "MENCARI...",
            showContent: "down",
            placeholder: "PILIH",
            showSearch: !0,
            searchFocus: !0,
            allowDeselect: !0,
            addToBody: !1,
            hideSelectedOption: !0,
            selectByGroup: !0,
            closeOnSelect: !0,
            addable: function (b) {
                return b;
            }
        })
    });
    pilihC.forEach(function (a) {
        new SlimSelect({
            select: a,
            searchPlaceholder: "CARI",
            searchText: "KOSONG",
            searchingText: "MENCARI...",
            showContent: "down",
            placeholder: "PILIH",
            showSearch: !0,
            searchFocus: !0,
            allowDeselect: !0,
            addToBody: !1,
            hideSelectedOption: !0,
            selectByGroup: !0,
            closeOnSelect: !0
        })
    });
    pilih.forEach(function (a) {
        new SlimSelect({
            select: a,
            showSearch: !1,
            searchFocus: !1,
            allowDeselect: !0,
            showContent: "down",
            addToBody: !1,
            hideSelectedOption: !0,
            selectByGroup: !0,
            closeOnSelect: !0
        })
    });
    pilihS.forEach(function (a) {
        new SlimSelect({
            select: a,
            showSearch: !1,
            searchFocus: !1,
            allowDeselect: !1,
            showContent: "down",
            addToBody: !1,
            hideSelectedOption: !0,
            selectByGroup: !1,
            closeOnSelect: !0
        })
    });
    var masukanI = document.querySelectorAll(".isian :is(textarea,input[type=text],input[type=search])");
    masukanI.forEach(function (a) {
        a.oninput = function () {
            this.value = this.value.toUpperCase()
        }
    });
    var tabelTH = document.querySelectorAll('#browser thead th'),
        baris = document.querySelectorAll('#browser tbody tr');
    [...baris].forEach(baris => {
        [...baris.cells].forEach((isi, indexisi) => {
            isi.dataset.th = [...tabelTH][indexisi].innerHTML;
        });
    });
</script>
@endsection