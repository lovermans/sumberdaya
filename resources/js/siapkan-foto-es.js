var SiapkanFoto = function (unggahan) {
    var berkas = unggahan.files[0],
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

export { SiapkanFoto as default };