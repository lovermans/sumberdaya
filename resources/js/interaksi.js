window.aplikasiSiap = true;

var range = document.createRange(),
    soket = null,
    judulHal = document.title;

document.addEventListener('click', function (e) {
    var navCb = document.getElementById('nav'),
        menuCb = document.getElementById('menu'),
        aplikasiCb = document.getElementById('pilih-aplikasi');

    if (e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var a = e.target.closest('a.nav-xhr, a.menu-xhr'),
            b = e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr');
        var ke = b.dataset.tujuan,
            pesan = b.dataset.pesan,
            metode = b.dataset.metode?.toUpperCase(),
            alamat = b.href,
            tanam = b.dataset.tanam,
            data = b.dataset.kirim,
            laju = b.dataset.laju,
            enkode = b.dataset.enkode == 'true' ? true : false,
            rekam = b.dataset.rekam,
            singkat = b.dataset.singkat == 'true' ? true : false,
            frag = b.dataset.frag == 'true' ? true : false,
            tn = b.dataset.tn == 'true' ? true : false,
            nn = b.dataset.nn == 'true' ? true : false,
            simpan = rekam == 'false' ? false : true;
        // if (!alamat.startsWith(location.origin)) {
        //     alamat = location.origin + alamat;
        // };
        if (a) {
            var navAktif = document.querySelectorAll('nav a.aktif, aside a.aktif'),
                appAktif = document.querySelectorAll('aside#menu-aplikasi a'),
                urlAktif = new URL(alamat);

            for (let z = 0; z < navAktif.length; z++) {
                navAktif[z].classList.remove('aktif');
            };

            a.classList.add('aktif');

            // if (appAktif && urlAktif.pathname.length > 1) {
            for (let m = 1; m < appAktif.length; m++) {
                let daftarmenu = new URL(appAktif[m].href);
                if (urlAktif.href.includes(daftarmenu.href)) {
                    appAktif[m].classList.add('aktif');
                };
            };
            // };
        };
        // if (location.href == alamat) {
        //     return;
        // };
        navCb.checked = false;
        menuCb.checked = false;
        aplikasiCb.checked = false;
        return lemparXHR({
            rekam: simpan,
            tujuan: ke,
            tautan: alamat,
            method: metode,
            pesanmuat: pesan,
            postdata: data,
            strim: laju,
            enkod: enkode,
            mintajs: singkat,
            topview: tn,
            normalview: nn,
            fragmen: frag,
            tanam: tanam
        });
    }
    if (e.target.matches('.menu-j')) {
        e.stopImmediatePropagation();
        return e.target.classList.toggle('aktif');
    }
    if (e.target.closest('button.tutup-i')) {
        e.stopImmediatePropagation();
        return e.target.closest('button.tutup-i').parentElement.remove();
    }
    if (e.target.closest('a.tutup-i')) {
        e.stopImmediatePropagation();
        return e.target.closest('a.tutup-i').parentNode.parentNode.parentNode.remove();
    }
    if (e.target.closest('.pesan-internal-tindaklanjut a.jtl')) {
        e.stopImmediatePropagation();
        e.preventDefault();
        localStorage.setItem('mengerti-mulai-aplikasi', true);
        return e.target.closest('.pesan-internal-tindaklanjut a.jtl').parentElement.parentElement.remove();
    }
    if (e.target.closest('#tombol-keluar-aplikasi')) {
        e.stopImmediatePropagation();
        e.preventDefault();
        document.getElementById('sematan_umum').replaceChildren();
        document.getElementById('nav').checked = false;
        document.getElementById('menu').checked = false;
        document.getElementById('pilih-aplikasi').checked = false;
        return document.getElementById('keluar-aplikasi').click();
    }
    if (e.target.closest('.tbl-btt')) {
        e.stopImmediatePropagation();
        e.preventDefault();
        return window.scrollTo(0, 0);
    }
    if (e.target.closest('button.ringkas-tabel')) {
        e.stopImmediatePropagation();
        e.preventDefault();
        return e.target.closest('button.ringkas-tabel').previousElementSibling.classList.toggle('ringkas');
    }
});

document.addEventListener('submit', function (e) {
    if (e.target.closest('.form-xhr')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var a = e.target.closest('.form-xhr');
        var alamat = a.dataset.tujuan,
            metode = a.method?.toUpperCase(),
            pesan = a.dataset.pesan,
            tanam = a.dataset.tanam,
            ke = a.dataset.blank == 'true' ? window.location.pathname : a.action,
            singkat = a.dataset.singkat == 'true' ? true : false,
            prog = a.dataset.laju == 'true' ? true : false,
            frag = a.dataset.frag == 'true' ? true : false,
            tn = a.dataset.tn == 'true' ? true : false,
            nn = a.dataset.nn == 'true' ? true : false,
            data = new FormData(a);

        // if (!ke.startsWith(location.origin)) {
        //     ke = location.origin + ke;
        // };

        if (metode == 'GET') {
            ke += '?' + new URLSearchParams(data).toString();
            var rekam = a.dataset.rekam,
                simpan = rekam == 'false' ? false : true;

            return lemparXHR({
                rekam: simpan,
                tujuan: alamat,
                tautan: ke,
                method: metode,
                pesanmuat: pesan,
                strim: prog,
                mintajs: singkat,
                topview: tn,
                normalview: nn,
                fragmen: frag,
                tanam: tanam
            });
        };

        if (metode == 'POST') {
            var rekam = a.dataset.rekam,
                simpan = rekam == 'true' ? true : false;

            return lemparXHR({
                rekam: simpan,
                tujuan: alamat,
                tautan: ke,
                method: metode,
                pesanmuat: pesan,
                postdata: data,
                strim: prog,
                mintajs: singkat,
                topview: tn,
                normalview: nn,
                fragmen: frag,
                tanam: tanam
            });
        };

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
        normalview = data.normalview ?? false,
        fragmen = data.fragmen ?? false,
        tanam = data.tanam ?? 'ganti',
        callback = data.callback ?? responUmum;
    var isi = document.querySelector(sisi) ?? document.querySelector('#isi') ?? document.querySelector('body');
    // if (!tautan.startsWith(location.origin)) {
    //     tautan = location.origin + tautan;
    // };
    if (mintajs) {
        isi = document.querySelector('#sematan_javascript');
        pesan = '';
    };
    if (rekam) {
        rekamTautan({ tujuan: sisi, tautan: tautan, method: metode, pesan: pesan, enkod: enkod, topview: topview, normalview: normalview, tanam: tanam });
    };
    // g ? isi.prepend(range.createContextualFragment('')) : isi.prepend(range.createContextualFragment(pesan));
    topview ? scrollTo(0, 0) :
        normalview ? scrollBy(0, 0) :
            isi.scrollIntoView();
    if (strim) {
        let lastResponseLength;
        let progressResponse;
        let responser;
        let isiRespon;
        let responseLength;

        for (var IDacak = '', b = 36; IDacak.length < 9;) {
            IDacak += (Math.random() * b | 0).toString(b);
        };
        var wadahPesan = '<div class="pesan tcetak">' +
            '<div id="' + IDacak + '"><p>Menunggu jawaban server...</div>' +
            '<button class="tutup-i"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"></path><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" ></path></svg ></button ></div > ';

        isi.prepend(range.createContextualFragment(wadahPesan));

        var isiPesan = document.getElementById(IDacak);

        xhr.onprogress = function (e) {

            responser = e.currentTarget.responseText;
            responseLength = responser.length;
            progressResponse = lastResponseLength ? responser.substring(lastResponseLength) : responser;
            lastResponseLength = responseLength;
            isiRespon = range.createContextualFragment(progressResponse);

            // isiPemberitahuan('pemberitahuan', '');
            // console.log(progressResponse);
            isiPesan.prepend(isiRespon);
            // return true;
        };

        xhr.onerror = function (er) {
            return;
        };
    } else {

        muat.classList.remove('mati');

        xhr.timeout = 60000;

        xhr.onload = function () {

            var xhrRes = {
                responUrl: xhr.responseURL,
                responXHR: xhr.responseText,
                responTujuan: xhr.getResponseHeader('X-Tujuan'),
                responHtml: xhr.getResponseHeader('Content-Type')?.startsWith('text/html'),
                responSVG: xhr.getResponseHeader('Content-Type')?.startsWith('image/svg+xml')
            };

            var dataReq = {
                isi: isi,
                pesan: pesan,
                metode: metode,
                muat: muat,
                rekam: rekam,
                tautan: tautan,
                postdata: postdata,
                strim: strim,
                enkod: enkod,
                mintajs: mintajs,
                topview: topview,
                normalview: normalview,
                fragmen: fragmen,
                tanam: tanam
            };

            callback({
                ...xhrRes,
                ...dataReq
            });
        };

        xhr.ontimeout = function (to) {
            muat.classList.add('mati');
            return;
        };

        xhr.onerror = function (er) {
            muat.classList.add('mati');
            return;
        };
    };
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
    if (soket) {
        xhr.setRequestHeader('X-Socket-ID', soket);
    }
    if (metode == 'POST') {
        xhr.send(postdata);
    }
    if (metode == 'GET') {
        xhr.send();
    }
};

function responUmum(data) {
    if (data.tautan !== data.responUrl && !data.responSVG) {
        if (data.responHtml) {
            rekamTautan({ tautan: data.responUrl });
        } else {
            return location.replace(data.responUrl);
        }
    };

    if (data.responXHR) {
        if (data.responXHR.startsWith('<!DOCTYPE html>')) {
            document.open();
            document.write(data.responXHR);
            document.close();
            return true;
        };

        if (data.responTujuan) {
            data.isi = document.getElementById(data.responTujuan) ?? document.querySelector('#isi') ?? document.querySelector('body');
        };

        data.tanam == 'append'
            ? data.isi.append(range.createContextualFragment(data.responXHR))
            : (
                data.tanam == 'prepend'
                    ? data.isi.prepend(range.createContextualFragment(data.responXHR))
                    : data.isi.replaceChildren(range.createContextualFragment(data.responXHR))
            );

        return data.muat.classList.add('mati');
    } else {
        return data.muat.classList.add('mati');
    };
};

function rekamTautan(data) {
    if (!data.tautan.startsWith(location.origin)) {
        data.tautan = location.origin + data.tautan;
    };
    var segmen = new URL(data.tautan).pathname.split('/');
    var judul = segmen[1] ? judulHal + ' - ' + segmen.join(' ') : judulHal;
    document.title = judul;

    return history.pushState({
        'tujuan': data.tujuan ?? null,
        'rute': data.tautan,
        'metode': data.method ?? 'GET',
        'pesan': data.pesan ?? null,
        'enkode': data.enkod ?? null,
        'topview': data.topview ?? false,
        'normalview': data.normalview ?? false,
        'tanam': data.tanam ?? null
    }, judul, data.tautan);
};

window.onpopstate = function (p) {
    if (p.state?.rute) {
        return lemparXHR({
            tujuan: p.state.tujuan,
            tautan: p.state.rute,
            method: p.state.metode,
            pesanmuat: p.state.pesan,
            enkod: p.state.enkode,
            topview: p.state.topview,
            normalview: p.state.normalview,
            tanam: p.state.tanam
        });
    }
};

window.isiPemberitahuan = function (a, b) {
    var f = document.getElementById(a);
    if (f) {
        b ? f.append(range.createContextualFragment(b)) : f.replaceChildren(range.createContextualFragment(b));
        return f.scrollIntoView();
    }
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
        muatSlimSelect({
            select: a,
            placeholder: 'PILIH',
            showSearch: false,
            searchFocus: false,
            allowDeselect: true,
            showContent: 'down',
            addToBody: false,
            hideSelectedOption: true,
            selectByGroup: true,
            closeOnSelect: true
        });
    });
};

window.pilSaja = function (a) {

    var ps = document.querySelectorAll(a);
    ps.forEach(function (a) {
        muatSlimSelect({
            select: a,
            placeholder: 'PILIH',
            showSearch: false,
            searchFocus: false,
            allowDeselect: false,
            showContent: 'down',
            addToBody: false,
            hideSelectedOption: true,
            selectByGroup: true,
            closeOnSelect: true
        });
    });
};

window.pilCari = function (a) {
    var ps = document.querySelectorAll(a);
    ps.forEach(function (a) {
        muatSlimSelect({
            select: a,
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
        });
    });
};

function urutIsi(a, b) {
    if (a.dataset.indeks < b.dataset.indeks)
        return -1;
    if (a.dataset.indeks > b.dataset.indeks)
        return 1;
    return 0;
};

window.urutData = function (a, b) {
    var indexes = document.querySelectorAll(b);
    var indexesArray = Array.from(indexes);
    let sorted = indexesArray.sort(urutIsi);
    sorted.forEach(e =>
        document.querySelector(a).appendChild(e)
    );
};
