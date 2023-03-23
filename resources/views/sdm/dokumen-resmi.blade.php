@extends('rangka')

@section('isi')
<div id="sdm_dokumen_resmi">
    <h4>Dokumen Resmi SDM</h4>

    @if($strRangka->contains($userRangka->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']))
    <details class="kartu">
        <summary>Standar Gaji</summary>
        
        <ol>
            @if ($strRangka->contains($userRangka->sdm_ijin_akses, ['KKA-BM', 'KKA-GG', 'KKA-BKM']) || blank($userRangka->sdm_ijin_akses))
            <li>
                <a href="{{ $storageRangka->disk('local')->temporaryUrl('sdm/panduan-pengurus/Standar Gaji Harian Mojokerto 2022.pdf', $dateRangka->now()->addMinutes(5)) }}" target="_blank">Standar Gaji Harian Mojokerto 2022.pdf</a>
            </li>
            @endif
            
            @if ($strRangka->contains($userRangka->sdm_ijin_akses, ['KKA-MKS']) || blank($userRangka->sdm_ijin_akses))
            <li>
                <a href="{{ $storageRangka->disk('local')->temporaryUrl('sdm/panduan-pengurus/Standar Gaji Harian Makassar 2022.pdf', $dateRangka->now()->addMinutes(5)) }}" target="_blank">Standar Gaji Harian Makassar 2022.pdf</a>
            </li>
            @endif
            
            @if ($strRangka->contains($userRangka->sdm_ijin_akses, ['KKA-SMG']) || blank($userRangka->sdm_ijin_akses))
            <li>
                <a href="{{ $storageRangka->disk('local')->temporaryUrl('sdm/panduan-pengurus/Standar Gaji Harian Semarang 2022.pdf', $dateRangka->now()->addMinutes(5)) }}" target="_blank">Standar Gaji Harian Semarang 2022.pdf</a>
            </li>
            @endif
        </ol>
    </details>
    @endif

    @isset($dokumenPengurus)
        @forelse ($dokumenPengurus as $jalur)
        <details class="kartu">
            <summary>{{ substr($jalur,21) }}</summary>
            
            <ol>
                @foreach ($storageRangka->files($jalur) as $berkas)
                <li>
                    <a href="{{ $storageRangka->disk('local')->temporaryUrl($berkas, $dateRangka->now()->addMinutes(5)) }}" target="_blank">{{ $appRangka->files->name($berkas).'.'.$appRangka->files->extension($berkas) }}</a>
                </li>
                @endforeach
            </ol>
        </details>
        
        @empty
        <p>Panduan Pengurus belum tersedia.</p>
        
        @endforelse
    @endisset
    
    @isset($dokumenUmum)
        @forelse ($dokumenUmum as $jalur)
        <details class="kartu" {{ substr($jalur,17) == 'Pengumuman' ? 'open' : '' }}>
            <summary>{{ substr($jalur,17) }}</summary>
    
            <ol>
                @foreach ($storageRangka->files($jalur) as $berkas)
                <li>
                    <a href="{{ $storageRangka->disk('local')->temporaryUrl($berkas, $dateRangka->now()->addMinutes(5)) }}" target="_blank">{{ $appRangka->files->name($berkas).'.'.$appRangka->files->extension($berkas) }}</a>
                </li>
                @endforeach
            </ol>
        </details>
        
        @empty
        <p>Panduan SDM belum tersedia.</p>
        
        @endforelse
    @endisset

    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#panahatas' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>
    
    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection