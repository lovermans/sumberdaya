! function () {
    window.addEventListener('DOMContentLoaded', function () {
        var e = document.getElementById('tema'),
            t = document.body;
        e.checked = 'true' === localStorage.getItem('tematerang'),
            t.setAttribute('data-tematerang', 'true' === localStorage.getItem('tematerang')),
            e.addEventListener('change', (function (e) {
                localStorage.setItem('tematerang', e.currentTarget.checked),
                    t.setAttribute('data-tematerang', e.currentTarget.checked)
            }));
    });
}();

var range = document.createRange();

document.addEventListener('click', function (e) {
    var navCb = document.getElementById('nav'),
        menuCb = document.getElementById('menu');

    if (e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr')) {
        e.preventDefault();
        var a = e.target.closest('a.nav-xhr, a.menu-xhr'),
            b = e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr');
        var tujuan = b.dataset.tujuan,
            pesan = b.dataset.pesan,
            metode = b.dataset.metode?.toUpperCase(),
            tautan = b.href,
            data = b.dataset.kirim,
            strim = b.dataset.laju,
            enkod = b.dataset.enkode,
            rekam = b.dataset.rekam,
            singkat = b.dataset.singkat,
            simpan = true;
        if (rekam == 'false') {
            simpan = false;
        }
        if (singkat == 'true') {
            singkat = true;
        } else { singkat = false; }
        if (enkod == 'true') {
            enkod = true;
        } else { enkod = false; }
        if (a) {
            var navAktif = document.querySelectorAll('nav a.aktif, aside a.aktif');
            for (let z = 0; z < navAktif.length; z++) {
                navAktif[z].classList.remove('aktif')
            };
            a.classList.add('aktif');
        }
        navCb.checked = false;
        menuCb.checked = false;
        return lemparXHR(simpan, tujuan, tautan, metode, pesan, data, strim, enkod, singkat);
    }
    if (e.target.matches('.menu-j')) {
        return e.target.classList.toggle('aktif');
    }
    if (e.target.closest('button.tutup-i')) {
        return e.target.closest('button.tutup-i').parentNode.remove();
    }
});

document.addEventListener('submit', function (e) {
    if (e.target.closest('.form-xhr')) {
        e.preventDefault();
        var a = e.target.closest('.form-xhr');
        var tujuan = a.dataset.tujuan,
            metode = a.method?.toUpperCase(),
            pesan = a.dataset.pesan,
            ke = a.action,
            singkat = a.dataset.singkat,
            prog = a.dataset.laju,
            data = new FormData(a);
        if (singkat == 'true') {
            singkat = true;
        } else { singkat = false; }
        if (prog == 'true') {
            prog = true;
        } else { prog = false; }
        if (metode == 'GET') {
            ke += '?' + new URLSearchParams(data).toString();
            var rekam = a.dataset.rekam,
                simpan = true;
            if (rekam == 'false') {
                simpan = false;
            }
            return lemparXHR(simpan, tujuan, ke, metode, pesan, null, prog, false, singkat);
        }
        if (metode == 'POST') {
            var rekam = a.dataset.rekam,
                simpan = false;
            if (rekam == 'true') {
                simpan = true;
            }
            return lemparXHR(simpan, tujuan, ke, metode, pesan, data, prog, false, singkat);
        }
        return alert('Periksa kembali formulir.');
    }
});

window.lemparXHR = function (a, b, c, d, e, f, g, h, i, j) {
    var xhr = new XMLHttpRequest(),
        sisi = b ?? '#isi',
        pesan = e ?? 'Menunggu jawaban server...',
        metode = d ?? 'GET';
    var isi = document.querySelector(sisi) ?? document.querySelector('#isi') ?? document.querySelector('body');
    if (i) {
        isi = document.querySelector('#sematan_javascript');
        pesan = '';
    };
    if (a) {
        rekamTautan(b, c, d, e, h);
    }
    isi.replaceChildren(pesan);
    j ? scrollTo(0,0) : isi.scrollIntoView();
    let lastResponseLength = false;
    if (g) {
        isi.replaceChildren('');
        xhr.onprogress = function (e) {
            let progressResponse;
            let responser = e.currentTarget.responseText;
            progressResponse = lastResponseLength ? responser.substring(lastResponseLength) : responser;
            lastResponseLength = responser.length;

            // if (c !== xhr.responseURL) {
            //     if (progressResponse.startsWith('<!DOCTYPE html>')) {
            //         document.open();
            //         document.write(progressResponse);
            //         document.close();
            //         return true;
            //     };
            //     document.querySelector('#isi').replaceChildren(range.createContextualFragment(progressResponse));
            //     return true;
            // };

            isiPemberitahuan('pemberitahuan', '');
            // console.log(progressResponse);
            isi.append(range.createContextualFragment(progressResponse));
            return true;
        };
    } else {
        xhr.onload = function () {
            // console.log(this.getAllResponseHeaders());
            // console.log(this.getResponseHeader('X-Kode-Javascript'));

            var responXHR = xhr.responseText;
            
            if (c !== xhr.responseURL) {
                rekamTautan(b, xhr.responseURL, 'GET', null);
            };

            if (responXHR) {
                if (responXHR.startsWith('<!DOCTYPE html>')) {
                    document.open();
                    document.write(responXHR);
                    document.close();
                    return true;
                };
                isiPemberitahuan('pemberitahuan', '');
                isi.replaceChildren(range.createContextualFragment(responXHR));
                j ? scrollTo(0,0) : isi.scrollIntoView();
                return true;
            };  
        };
    }
    xhr.open(metode, c, true);
    xhr.setRequestHeader('X-PJAX', true);
    if (h) {
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    }
    if (i) {
        xhr.setRequestHeader('X-Minta-Javascript', true);
    }
    if (metode == 'POST') {
        xhr.send(f);
    }
    if (metode == 'GET') {
        xhr.send();
    }
};

function rekamTautan(a, b, c, d, e) {
    var segmen = b.split('/');
    var judul = document.title + ' - ' + segmen[1];
    history.pushState({
        'tujuan': a,
        'rute': b,
        'metode': c,
        'pesan': d,
        'enkode': e
    }, judul, b);
};

window.onpopstate = function (p) {
    if (p.state?.rute) {
        lemparXHR(false, p.state.tujuan, p.state.rute, p.state.metode, p.state.pesan, null, false, p.state.enkode, false);
    } else { location = window.location.href };
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