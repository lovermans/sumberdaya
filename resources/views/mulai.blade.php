@extends('rangka')

@section('isi')
<div id="mulai-aplikasi">
    @if(!$userRangka)
    <form class="form-xhr kartu" method="POST" data-tn="true" action="{{ $urlRangka->route('login', [], false) }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        
        <div class="isian normal">
            <label for="idAbsen">Nomor Absen</label>
            
            <input id="idAbsen" type="text" name="sdm_no_absen" value="{{ $rekRangka->old('sdm_no_absen') }}" pattern="^[0-9]{8}$"
                inputmode="numeric" required>
            
            <span class="t-bantu">8 digit nomor absen</span>
        </div>
        
        <div class="isian normal">
            <label for="password">Sandi</label>
            
            <input id="password" type="password" name="password" required>
            
            <span class="t-bantu">Sandi akun</span>
        </div>
        
        <div class="gspan-4"></div>
        
        <button class="utama pelengkap" type="submit">MASUK</button>
    </form>

    @else
    <div id="mengerti-mulai-aplikasi" class="pesan-internal">
        <p>
            Tekan/sentuh tombol
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#aplikasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            di ujung kanan atas layar untuk memilih aplikasi.
        </p>
        
        <a class="sekunder" href="#" onclick="event.preventDefault();
            localStorage.setItem('mengerti-mulai-aplikasi', true);
            this.parentElement.remove()">
            MENGERTI
        </a>
        
        <div class="bersih"></div>
    </div>

    <script>
        if(localStorage.getItem('mengerti-mulai-aplikasi') == 'true') document.getElementById('mengerti-mulai-aplikasi').remove();
    </script>

    @endif

    <div class="kartu">
        <h2>Sejarah</h2>

        <p>PT. Kepuh Kencana Arum didirikan pada tahun 1991 di Mojokerto, Jawa Timur. <b>"KENCANA"</b> adalah merk yang
            diperkenalkan kepada masyarakat oleh PT. Kepuh Kencana Arum.</p>
        <p>Awal mulanya PT. Kepuh Kencana Arum memproduksi atap metal gelombang tanpa sambungan lalu seiring berjalannya
            waktu juga menambah ragam produk berupa genteng metal, rangka atap, rangka plafon, penutup plafon, rangka
            partisi dan lain-lain.</p>
        <p>Dengan keahlian, ketekunan, pengalaman serta dedikasi seluruh elemen Perusahaan, maka PT. Kepuh Kencana Arum
            telah berhasil memenuhi kualitas produk sesuai dengan Standar Nasional Indonesia (SNI) dan meningkatkan kapasitas
            produksinya dengan mendirikan pabrik di Bogor pada tahun 1997, Makassar pada tahun 2002, Semarang pada tahun 2010,
            dan Bandung pada tahun 2020 serta menjalin kerja sama dengan pemasok bahan baku yang memiliki standar
            internasional dan memenuhi Standar Nasional Indonesia (SNI).
        <p>
        <p>Hingga saat ini telah berkembang juga merk <b>"VIVO"</b> yang telah banyak beredar sebagai pendukung merk utama
            <b>"KENCANA"</b> di berbagai daerah di Nusantara. Kini PT. Kepuh Kencana Arum juga mendapat kepercayaan dari PT.
            Krakatau Steel melalui kerjasama produksi untuk produk-produk baja ringan yang menggunakan merk Krakatau Steel.
        </p>
    </div>

    <div class="kartu">
        <h2>Visi</h2>

        <p><b>"Menjadi produsen bahan bangunan terbesar di Indonesia dan Asia Tenggara"</b></p>
    </div>

    <div class="kartu">
        <h2>Misi</h2>

        <ol>
            <li>Menanam budaya kejujuran kepada karyawan, pelanggan dan pemasok.</li>
            <li>Menjamin ketersediaan produk yang berkualitas di seluruh area.</li>
            <li>Memberikan harga yang kompetitif.</li>
            <li>Menjamin kecepatan produksi dan pengiriman.</li>
            <li>Memberikan dukungan teknis dan layanan purnal.</li>
        </ol>
    </div>

    <div class="kartu">
        <h2>Nilai Budaya</h2>

        <ol>
            <li><b>Kejujuran</b> : <q>Jujur baik secara pribadi maupun dalam bekerja</q></li>
            <li><b>Integritas</b> : <q>Setia pada kebenaran dan menjunjung tinggi kesetiaan</q></li>
            <li><b>Dedikasi</b> : <q>Bertanggung-jawab atas apa yang menjadi komitmen sejak awal</q></li>
            <li><b>Loyalitas</b> : <q>Loyalitas akan timbul sebagai buah nilai kejujuran, integritas dan dedikasi</q></li>
        </ol>
    </div>

    <div class="kartu">
        <h2>Kebijakan Mutu</h2>

        <ol>
            <li>Melaksanakan proses yang efektif dan efisien dan berpedoman pada Sistem Manajemen Mutu ISO 9001 dan terus
                berusaha melakukan peningkatan yang berkesinambungan di dalam Sistem Manajemen Mutu Perusahaan.</li>
            <li>Memenuhi persyaratan pelanggan dan peraturan perundang-undangan yang berlaku demi tercapainya kepuasan dan
                loyalitas pelanggan dan pihak-pihak terkait lainnya.</li>
            <li>Terus meningkatkan kualitas, ketepatan waktu produksi dan pengiriman serta inovasi produk.</li>
            <li>Mengutamakan kejujuran personil dalam memberikan informasi dan saran kepada pelanggan.</li>
            <li>Mengutamakan untuk memberikan material berkualitas dan bernilai tambah dengan harga kompetitif kepada
                seluruh pelanggan.</li>
            <li>Meningkatkan potensi tenaga kerja di perusahaan dengan prinsip <i>long life learning</i>.</li>
            <li>Ikut serta dalam kegiatan sosial dan pembangunan ekonomi Nasional.</li>
        </ol>
    </div>

    <div class="kartu">
        <h2>Wilayah Operasional</h2>
        
        <ol>
            <li><b>Pusat Pemasaran</b> : <a href="https://goo.gl/maps/A2gJWm7A7byQLWCd6" target="_blank"
                    rel="noreferrer noopener">Jln Bubutan 127-135, Bubutan, Bubutan, Surabaya, Jawa Timur 60174.</a></li>
            <li><b>Pusat Administrasi</b> : <a href="https://goo.gl/maps/WHrDZVw46Vw" target="_blank"
                    rel="noreferrer noopener">Jln WR Supratman 53, Purwotengah, Kranggan, Mojokerto, Jawa Timur 61311.</a>
            </li>
            <li><b>Pusat Pabrik</b> : <a href="https://goo.gl/maps/yZHPoN5G4iKw3FCH9" target="_blank"
                    rel="noreferrer noopener">Jln Raya Bypass KM 54, Balongmojo, Puri, Mojokerto, Jawa Timur 61363.</a></li>
            <li><b>Pabrik Gunung Gedangan</b> : <a href="https://goo.gl/maps/qsSdaj6imhNqm5oc9" target="_blank"
                    rel="noreferrer noopener">Jln Al Azhar (Depan Pondok Al Azhar), Gunung Gedangan, Magersari, Mojokerto,
                    Jawa Timur 61363.</a></li>
            <li><b>Pabrik Semarang</b> : <a href="https://goo.gl/maps/hjAXTbTbJheGRmG9A" target="_blank"
                    rel="noreferrer noopener">Jln Raya Semarang-Solo KM 25 (Sebelah SPBU Lemah Abang) RT 1 RW 5, Karangjati,
                    Bergas, Semarang, Jawa Tengah 50552.</a></li>
            <li><b>Pabrik Bogor</b> : <a href="https://goo.gl/maps/65mYN19chjfjjB2D7" target="_blank"
                    rel="noreferrer noopener">Jln Melati 65, Wanaherang, Gunung Putri, Bogor, Jawa Barat 16965.</a></li>
            <li><b>Pabrik Makassar</b> : <a href="https://goo.gl/maps/Y9YEKm7UhWFWHZPS6" target="_blank"
                    rel="noreferrer noopener">Jln Prof Dr Ir Sutami 88, Bulurokeng, Biringkanaya, Makassar, Sulawesi Selatan
                    90242.</a></li>
            <li><b>Pabrik Bandung</b> : <a href="https://goo.gl/maps/TPNqo8EsMhyr4ehU8" target="_blank"
                    rel="noreferrer noopener">Jln Sukarno-Hatta 734, Cipayung Kidul, Panyileukan, Bandung, Jawa Barat
                    40164.</a></li>
        </ol>
    </div>

    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#panahatas' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>
    
    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
    @include('komponen')

    @if($userRangka && $rekRangka->pjax())
    <script>
        cariElemen("#menu-aplikasi a[href='/']").then((el) => {el.classList.add("aktif");});
    </script>
    @endif
</div>
@endsection