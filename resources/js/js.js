window.aplikasiSiap = function () {
    return true;
};

var range = document.createRange(),
    judulHal = document.title;

document.addEventListener('click', function (e) {
    var navCb = document.getElementById('nav'),
        menuCb = document.getElementById('menu');

    if (e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr')) {
        e.preventDefault();
        var a = e.target.closest('a.nav-xhr, a.menu-xhr'),
            b = e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr');
        var ke = b.dataset.tujuan,
            pesan = b.dataset.pesan,
            metode = b.dataset.metode?.toUpperCase(),
            alamat = b.href,
            data = b.dataset.kirim,
            laju = b.dataset.laju,
            enkode = b.dataset.enkode == 'true' ? true : false,
            rekam = b.dataset.rekam,
            singkat = b.dataset.singkat == 'true' ? true : false,
            frag = b.dataset.frag == 'true' ? true : false,
            tn = b.dataset.tn == 'true' ? true : false,
            simpan = rekam == 'false' ? false : true;
        if (a) {
            var navAktif = document.querySelectorAll('nav a.aktif, aside a.aktif');
            for (let z = 0; z < navAktif.length; z++) {
                navAktif[z].classList.remove('aktif')
            };
            a.classList.add('aktif');
        }
        navCb.checked = false;
        menuCb.checked = false;
        return lemparXHR({
            rekam : simpan,
            tujuan : ke,
            tautan : alamat,
            method : metode,
            pesanmuat : pesan,
            postdata : data,
            strim : laju,
            enkod : enkode,
            mintajs : singkat,
            topview : tn,
            fragmen : frag
        });
    }
    if (e.target.matches('.menu-j')) {
        return e.target.classList.toggle('aktif');
    }
    if (e.target.closest('button.tutup-i')) {
        return e.target.closest('button.tutup-i').parentNode.remove();
    }
    if (e.target.closest('a.tutup-i')) {
        return e.target.closest('a.tutup-i').parentNode.parentNode.parentNode.remove();
    }
});

document.addEventListener('submit', function (e) {
    if (e.target.closest('.form-xhr')) {
        e.preventDefault();
        var a = e.target.closest('.form-xhr');
        var alamat = a.dataset.tujuan,
            metode = a.method?.toUpperCase(),
            pesan = a.dataset.pesan,
            ke = a.dataset.blank == 'true' ? window.location.pathname : a.action,
            singkat = a.dataset.singkat == 'true' ? true : false,
            prog = a.dataset.laju == 'true' ? true : false,
            frag = a.dataset.frag == 'true' ? true : false,
            tn = a.dataset.tn == 'true' ? true : false,
            data = new FormData(a);
            console.log(a.action);
        if (metode == 'GET') {
            ke += '?' + new URLSearchParams(data).toString();
            var rekam = a.dataset.rekam,
                simpan = rekam == 'false' ? false : true;
            return lemparXHR({
                rekam : simpan,
                tujuan : alamat,
                tautan : ke,
                method : metode,
                pesanmuat : pesan,
                strim : prog, 
                mintajs : singkat,
                topview : tn,
                fragmen : frag
            });
        }
        if (metode == 'POST') {
            var rekam = a.dataset.rekam,
                simpan = rekam == 'true' ? true : false;
            return lemparXHR({
                rekam : simpan,
                tujuan : alamat,
                tautan : ke,
                method : metode,
                pesanmuat : pesan,
                postdata : data,
                strim : prog,
                mintajs : singkat,
                topview : tn,
                fragmen : frag
            });
        }
        return alert('Periksa kembali formulir.');
    }
});

window.lemparXHR = function (data) {
    var xhr = new XMLHttpRequest(),
        sisi = data.tujuan ?? '#isi',
        pesan = data.pesanmuat ?? '',
        metode = data.method ?? 'GET',
        muat = document.getElementById('memuat'),
        rekam = data.rekam ?? false,
        tautan = data.tautan ?? null,
        postdata = data.postdata ?? null,
        strim = data.strim ?? false,
        enkod = data.enkod ?? false,
        mintajs = data.mintajs ?? false,
        topview = data.topview ?? false,
        fragmen = data.fragmen ?? false;
    var isi = document.querySelector(sisi) ?? document.querySelector('#isi') ?? document.querySelector('body');
    if (!tautan.startsWith(location.origin)) {
        tautan = location.origin + tautan;
    }
    if (mintajs) {
        isi = document.querySelector('#sematan_javascript');
        pesan = '';
    };
    if (rekam) {
        rekamTautan({tujuan : sisi, tautan : tautan, method : metode, pesan : pesan, enkod : enkod});
    }
    // g ? isi.prepend(range.createContextualFragment('')) : isi.prepend(range.createContextualFragment(pesan));
    topview ? scrollTo(0,0) : isi.scrollIntoView();
    if (strim) {
        let lastResponseLength;
        let progressResponse;
        let responser;
        let isiRespon;
        let responseLength;

        for(var IDacak = '', b = 36; IDacak.length < 9;) {
            IDacak += (Math.random() * b | 0).toString(b);
        };
        var wadahPesan = '<div class="pesan tcetak">' +
            '<div id="' + IDacak + '"><p>Menunggu jawaban server...</div>' +
            '<button class="tutup-i"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="/ikon.svg?id=ec47ccd0fadc02f2d210d55d23c3c657#tutup" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></button></div>';
        
        isi.prepend(range.createContextualFragment(wadahPesan));

        var isiPesan = document.getElementById(IDacak);

        xhr.onprogress = function (e) {
            
            responser = e.currentTarget.responseText;
            responseLength = responser.length;
            progressResponse = lastResponseLength ? responser.substring(lastResponseLength) : responser;
            isiRespon = range.createContextualFragment(progressResponse);
            
            // isiPemberitahuan('pemberitahuan', '');
            // console.log(progressResponse);
            isiPesan.prepend(isiRespon);
            lastResponseLength = responseLength;
            // return true;
         
        };
    } else {

        muat.classList.toggle('mati');

        xhr.onload = function () {
            // console.log(this.getAllResponseHeaders());
            // console.log(this.getResponseHeader('X-Kode-Javascript'));

            if (tautan !== xhr.responseURL) {
                if (xhr.getResponseHeader('Content-Type').startsWith('text/html')) {
                    rekamTautan({tautan : xhr.responseURL});
                } else {
                    location = xhr.responseURL;
                    return true;
                }
            };

            var responXHR = xhr.responseText,
            responTujuan = xhr.getResponseHeader('X-Tujuan');

            if (responXHR) {
                if (responXHR.startsWith('<!DOCTYPE html>')) {
                    document.open();
                    document.write(responXHR);
                    document.close();
                    return true;
                };
                // if(!j){
                //     isiPemberitahuan('pemberitahuan', '');
                // }
                if (responTujuan) {
                    isi = document.getElementById(responTujuan) ?? document.querySelector('#isi') ?? document.querySelector('body');
                }
                isi.replaceChildren(range.createContextualFragment(responXHR));
                topview ? scrollTo(0,0) : isi.scrollIntoView();
                muat.classList.toggle('mati');
                return true;
            };  
        };
    }
    xhr.open(metode, tautan, true);
    xhr.setRequestHeader('X-PJAX', true);
    if (enkod) {
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    }
    if (fragmen) {
        xhr.setRequestHeader('X-Frag', true);
    }
    if (mintajs) {
        xhr.setRequestHeader('X-Minta-Javascript', true);
    }
    if (metode == 'POST') {
        xhr.send(postdata);
    }
    if (metode == 'GET') {
        xhr.send();
    }
};

function rekamTautan(data) {
    var segmen = new URL(data.tautan).pathname.split('/');
    var judul = segmen[1] ? judulHal + ' - ' + segmen.join(' ') : judulHal;
    document.title = judul;
    history.pushState({
        'tujuan': data.tujuan ?? null,
        'rute': data.tautan,
        'metode': data.method ?? 'GET',
        'pesan': data.pesan ?? null,
        'enkode': data.enkod ?? null
    }, judul, data.tautan);
};

window.onpopstate = function (p) {
    if (p.state?.rute) {
        lemparXHR({
            tujuan : p.state.tujuan,
            tautan : p.state.rute,
            method : p.state.metode,
            pesanmuat : p.state.pesan,
            enkod : p.state.enkode
        });
    } else { 
        location.reload(); 
    };
};

window.siapkanFoto = function (a) {
    var berkas = a.files[0],
        lihat = document.getElementById('foto'),
        profilSvg = document.querySelector('#foto.svg');
    if (berkas.type.match(/image.*/)) {
        var baca = new FileReader();
        baca.onload = function (b) {
            var gambar = new Image();
            gambar.onload = function (c) {
                var MAX_WIDTH = 300,
                    outputImageAspectRatio = 3 / 4;
                var inputWidth = c.target.width;
                var inputHeight = c.target.height;
                var inputImageAspectRatio = inputWidth / inputHeight;
                let outputWidth = inputWidth;
                let outputHeight = inputHeight;
                if (inputImageAspectRatio > outputImageAspectRatio) {
                    outputWidth = inputHeight * outputImageAspectRatio;
                } else if (inputImageAspectRatio < outputImageAspectRatio) {
                    outputHeight = inputWidth / outputImageAspectRatio;
                }
                var outputX = (inputWidth - outputWidth) * 0.5;
                var outputY = (inputHeight - outputHeight) * 0.5;
                var canvas = document.createElement('canvas');
                canvas.width = MAX_WIDTH;
                canvas.height = MAX_WIDTH / outputImageAspectRatio;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(c.target, outputX, outputY, outputWidth, outputHeight, 0, 0, canvas.width, canvas.height);
                var hasil = ctx.canvas.toDataURL('image/webp');
                if (profilSvg) {
                    profilSvg.classList.remove('svg');
                }
                lihat.src = hasil;
                canvas.toBlob(function (blob) {
                    var namaBerkas = 'foto-profil.webp'
                    var berkasBaru = new File([blob], namaBerkas, { type: blob.type, lastModified: new Date().getTime() });
                    var ganti = new DataTransfer();
                    ganti.items.add(berkasBaru);
                    document.querySelector('#foto_profil').files = ganti.files;
                }, 'image/webp');
            }
            return gambar.src = b.target.result;
        }
        baca.readAsDataURL(berkas);
    }
    else {
        return alert('Berkas yang diunggah wajib berupa gambar');
    }
};

window.isiPemberitahuan = function (a, b) {
    var f = document.getElementById(a);
    if (f) {
        b ? f.append(range.createContextualFragment(b)) : f.replaceChildren(range.createContextualFragment(b));
        f.scrollIntoView();
    }
};

window.ringkasTabel = function (a) {
    a.previousElementSibling.classList.toggle('ringkas');
};

window.formatTabel = function (a, b) {
    var tabelTH = document.querySelectorAll(a),
        baris = document.querySelectorAll(b);
    [...baris].forEach(baris => {
        [...baris.cells].forEach((isi, indexisi) => {
            isi.dataset.th = [...tabelTH][indexisi].innerHTML;
        });
    });
};

window.formatIsian = function (a) {
    var ipt = document.querySelectorAll(a);
    ipt.forEach(function (a) {
        a.oninput = function () {
            var cmulai = this.selectionStart,
                cakhir = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(cmulai, cakhir);
        }
    });
};

window.pilDasar = function (a) {
    var ps = document.querySelectorAll(a);
    ps.forEach(function (a) {
        new SlimSelect({
            select: a,
            // settings: {
                placeholder: 'PILIH',
                showSearch: false,
                searchFocus: false,
                allowDeselect: true,
                showContent: 'down',
                addToBody: false,
                hideSelectedOption: true,
                selectByGroup: true,
                closeOnSelect: true
            // }
        });
    });
};

window.pilSaja = function (a) {
    var ps = document.querySelectorAll(a);
    ps.forEach(function (a) {
        new SlimSelect({
            select: a,
            // settings: {
                placeholder: 'PILIH',
                showSearch: false,
                searchFocus: false,
                allowDeselect: false,
                showContent: 'down',
                addToBody: false,
                hideSelectedOption: true,
                selectByGroup: true,
                closeOnSelect: true
            // }
        });
    });
};

window.pilCari = function (a) {
    var pc = document.querySelectorAll(a);
    pc.forEach(function (a) {
        new SlimSelect({
            select: a,
            // settings: {
                searchPlaceholder: 'CARI',
                searchText: 'KOSONG',
                searchingText: 'MENCARI...',
                showContent: 'down',
                placeholder: 'PILIH',
                showSearch: true,
                searchFocus: true,
                allowDeselect: true,
                addToBody: false,
                hideSelectedOption: true,
                selectByGroup: true,
                closeOnSelect: true
            // }
        });
    });
};

function urutIsi(a, b) {
    if (a.dataset.indeks < b.dataset.indeks)
        return -1;
    if (a.dataset.indeks > b.dataset.indeks)
        return 1;
    return 0;
}

window.urutData = function (a, b) {
    var indexes = document.querySelectorAll(b);
    var indexesArray = Array.from(indexes);
    let sorted = indexesArray.sort(urutIsi);
    sorted.forEach(e =>
        document.querySelector(a).appendChild(e));
}