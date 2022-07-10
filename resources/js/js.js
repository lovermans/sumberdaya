! function () {
    var e = document.getElementById("tema"),
        t = document.body;
    e.checked = "true" === localStorage.getItem("tematerang"), t.setAttribute("data-tematerang", "true" === localStorage.getItem("tematerang")), e.addEventListener("change", (function (e) {
        localStorage.setItem("tematerang", e.currentTarget.checked), t.setAttribute("data-tematerang", e.currentTarget.checked)
    }))
}();

var navCb = document.querySelector("#nav"),
    menuCb = document.querySelector("#menu"),
    range = document.createRange();

document.addEventListener('click', function (e) {
    if (e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr')) {
        e.preventDefault();
        var a = e.target.closest('a.nav-xhr'),
            b = e.target.closest('a.nav-xhr, a.menu-xhr, a.isi-xhr');
        var tujuan = b.dataset.tujuan ?? 'main section';
        if (a) {
            var navAktif = document.querySelectorAll("nav a.aktif");
            for (var z = 0; z < navAktif.length; z++) {
                navAktif[z].classList.remove("aktif")
            };
            a.classList.add("aktif");
        }
        navCb.checked = false;
        menuCb.checked = false;
        window.lemparXHR(true, tujuan, b.href, 'GET');
    }
    if (e.target.matches('.menu-j')) {
        e.target.classList.toggle('aktif');
    }
    if (e.target.closest('button.tutup-i')) {
        e.target.closest('button.tutup-i').parentNode.remove();
    }
});

document.addEventListener('submit', function (e) {
    if (e.target.closest('.form-xhr')) {
        e.preventDefault();
        var a = e.target.closest('.form-xhr');
        var tujuan = a.dataset.tujuan ?? 'main section #isi',
            metode = a.method,
            ke = a.action,
            data = new FormData(a);
        if (metode == 'GET') {
            var tautan = ke + '?' + new URLSearchParams(data).toString();
            window.lemparXHR(true, tujuan, tautan, 'GET');
        } else {
            window.lemparXHR(false, tujuan, ke, 'POST', data);
        }
    }
});

window.lemparXHR = function (a, b, c, d, e) {

    if (a) {
        rekamTautan(b, c, d, e);
    }
    var xhr = new XMLHttpRequest(),
        isi = document.querySelector(b);
    isi.replaceChildren('Sedang bekerja...');
    isi.scrollIntoView();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4) {
            var responXHR = xhr.responseText;
            if (c !== xhr.responseURL) {
                rekamTautan(b, xhr.responseURL, d, e);
            };
            if (responXHR) {
                if (responXHR.match(/(DOCTYPE)/)) { tanganiHTML(responXHR); };
                isi.replaceChildren(range.createContextualFragment(responXHR));
                isi.scrollIntoView();
            };
        }
    };
    xhr.open(d, c, true);
    xhr.setRequestHeader('X-PJAX', true);
    xhr.send(e);
};

function rekamTautan(a, b, c, d) {
    history.pushState({
        'isi': a,
        'rute': b,
        'met': c,
        'data': d
    }, document.title, b);
};

function tanganiHTML(p) {
    document.open();
    document.write(p);
    document.close();
};
window.onpopstate = function (p) {
    if (!p.state) {
        location = window.location.href;
    } else {
        window.lemparXHR(true, p.state.isi, p.state.rute, p.state.met, p.state.data)
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
                var MAX_WIDTH = 200,
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
                var ctx = canvas.getContext("2d");
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
            gambar.src = b.target.result;
        }
        baca.readAsDataURL(berkas);
    }
    else {
        alert('Berkas yang diunggah wajib berupa gambar');
    }
}