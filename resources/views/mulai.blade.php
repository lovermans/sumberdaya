@extends('rangka') @section('isi')
    @guest <form class="kartu" method="POST" action="{{ route('login') }}"> @csrf
            <div class="isian gspan-2"> <label for="idAbsen">Nomor Absen</label> <input id="idAbsen" type="text"
                    name="sdm_no_absen" value="{{ old('sdm_no_absen') }}" pattern="^[0-9]{8}$" inputmode="numeric" required>
                <span class="t-bantu">8 digit nomor absen</span> </div>
            <div class="isian gspan-2"> <label for="password">Sandi</label> <input id="password" type="password" name="password"
                    required> <span class="t-bantu">Sandi akun</span> </div> <button class="utama gspan-4"
                type="submit">MASUK</button>
        </form>
    @endguest
    
    @auth <div class="isian"> <label for="sumber_daya">Akses</label> <select
                name="sumber_daya" id="sumber_daya" class="pil-cari"
                onchange="if (this.value !== '') location = this.value;">
                <option value=""></option> @can('sdm-akses')
                    <option value="{{ route('sdm.mulai') }}">SUMBER DAYA MANUSIA</option>
                @endcan
            </select> <span class="t-bantu">Pilih sumber daya yang akan dikelola </span> </div>
    <script>
        var pilihC = document.querySelectorAll(".pil-cari");
        pilihC.forEach(function(a) {
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
    </script> @endauth <h2>Sejarah</h2>
    <div class="kartu">
        <p>PT. Kepuh Kencana Arum didirikan pada tahun 1991 di Mojokerto, Jawa Timur. <b>"KENCANA"</b> adalah merk yang
            diperkenalkan kepada masyarakat oleh PT. Kepuh Kencana Arum.</p>
        <p>Pada mulanya PT. Kepuh Kencana Arum memproduksi atap metal gelombang tanpa sambungan lalu seiring berjalannya
            waktu juga menambah ragam produk berupa genteng metal, rangka atap, rangka plafon, penutup plafon, rangka
            partisi dan lain-lain.</p>
        <p>Dengan keahlian, ketekunan, pengalaman serta dedikasi seluruh elemen Perusahaan, maka PT. Kepuh Kencana Arum
            telah berhasil meningkatkan kualitas produk yang sesuai dengan Standar Nasional Indonesia (SNI) dan kapasitas
            produksi dengan mendirikan pabrik di Bogor pada tahun 1997, Makassar pada tahun 2002, Semarang pada tahun 2010,
            dan Bandung pada tahun 2020, serta menjalin kerja sama dengan pemasok bahan baku yang memiliki standar
            internasional dan memenuhi standar nasional Indonesia (SNI).
        <p>
        <p>Hingga saat ini telah berkembang juga merk <b>"VIVO"</b> yang telah banyak beredar sebagai pendukung merk utama
            <b>"KENCANA"</b> di berbagai daerah di Nusantara. Kini PT. Kepuh Kencana Arum juga mendapat kepercayaan dari PT.
            Krakatau Steel melalui kerjasama produksi untuk produk-produk baja ringan yang menggunakan merk Krakatau Steel.
        </p>
    </div>
    <h2>Visi</h2>
    <div class="kartu">
        <p><b>"Menjadi produsen bahan bangunan terbesar di Indonesia dan Asia Tenggara"</b></p>
    </div>
    <h2>Misi</h2>
    <div class="kartu">
        <ol>
            <li>Menanam budaya kejujuran kepada karyawan, pelanggan dan pemasok.</li>
            <li>Menjamin ketersediaan produk yang berkualitas di seluruh area.</li>
            <li>Memberikan harga yang kompetitif.</li>
            <li>Menjamin kecepatan produksi dan pengiriman.</li>
            <li>Memberikan dukungan teknis dan layanan purnal.</li>
        </ol>
    </div>
    <h2>Nilai Budaya</h2>
    <div class="kartu">
        <ol>
            <li><b>Kejujuran</b> : <q>Jujur baik secara pribadi maupun dalam bekerja</q></li>
            <li><b>Integritas</b> : <q>Setia pada kebenaran dan menjunjung tinggi kesetiaan</q></li>
            <li><b>Dedikasi</b> : <q>Bertanggung-jawab atas apa yang menjadi komitmen sejak awal</q></li>
            <li><b>Loyalitas</b> : <q>Loyalitas akan timbul sebagai buah nilai kejujuran, integritas dan dedikasi</q></li>
        </ol>
    </div>
    <h2>Kebijakan Mutu</h2>
    <div class="kartu">
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
    <h2>Wilayah Operasional</h2>
    <div class="kartu">
        <ol>
            <li><b>Pusat Pemasaran</b> : <a href="https://goo.gl/maps/DtG6pibT2ejxQ5MK7" target="_blank"
                    rel="noreferrer noopener">Jln Bubutan 127-135, Bubutan, Bubutan, Surabaya, Jawa Timur 60174.</a></li>
            <li><b>Pusat Administrasi</b> : <a href="https://goo.gl/maps/WHrDZVw46Vw" target="_blank"
                    rel="noreferrer noopener">Jln WR Supratman 53, Purwotengah, Kranggan, Mojokerto, Jawa Timur 61311.</a>
            </li>
            <li><b>Pusat Pabrik</b> : <a href="https://goo.gl/maps/AEZNau7xhhD2" target="_blank"
                    rel="noreferrer noopener">Jln Raya Bypass KM 54, Balongmojo, Puri, Mojokerto, Jawa Timur 61363.</a></li>
            <li><b>Pabrik Gunung Gedangan</b> : <a href="https://goo.gl/maps/qsSdaj6imhNqm5oc9" target="_blank"
                    rel="noreferrer noopener">Jln Al Azhar (Depan Pondok Al Azhar), Gunung Gedangan, Magersari, Mojokerto,
                    Jawa Timur 61363.</a></li>
            <li><b>Pabrik Semarang</b> : <a href="https://goo.gl/maps/hjAXTbTbJheGRmG9A" target="_blank"
                    rel="noreferrer noopener">Jln Raya Semarang-Solo KM 25 (Sebelah SPBU Lemah Abang) RT 1 RW 5, Karangjati,
                    Bergas, Semarang, Jawa Tengah 50552.</a></li>
            <li><b>Pabrik Bogor</b> : <a href="https://goo.gl/maps/hjAXTbTbJheGRmG9A" target="_blank"
                    rel="noreferrer noopener">Jln Melati 65, Wanaherang, Gunung Putri, Bogor, Jawa Barat 16965.</a></li>
            <li><b>Pabrik Makassar</b> : <a href="https://goo.gl/maps/65mYN19chjfjjB2D7" target="_blank"
                    rel="noreferrer noopener">Jln Prof Dr Ir Sutami 88, Bulurokeng, Biringkanaya, Makassar, Sulawesi Selatan
                    90242.</a></li>
            <li><b>Pabrik Bandung</b> : <a href="https://goo.gl/maps/Pnhh9yR92aaRHH4j6" target="_blank"
                    rel="noreferrer noopener">Jln Sukarno-Hatta 734, Cipayung Kidul, Panyileukan, Bandung, Jawa Barat
                    40164.</a></li>
        </ol>
    </div>
    @includeWhen(session()->has('spanduk') || session()->has('pesan') || $errors->any(), 'pemberitahuan')
@endsection
