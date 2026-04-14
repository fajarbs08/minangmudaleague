import Swal from 'sweetalert2';

//Basic
if (document.getElementById("sweetalert-basic"))
    document.getElementById("sweetalert-basic").addEventListener("click", function () {
        Swal.fire({
            title: 'Semua orang bisa menggunakan komputer',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            showCloseButton: false
        })
    });

//A title with a text under
if (document.getElementById("sweetalert-title"))
    document.getElementById("sweetalert-title").addEventListener("click", function () {
        Swal.fire({
            title: "Internet?",
            text: 'Masih ada sampai sekarang?',
            icon: 'question',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            showCloseButton: false
        })
    });

//Success Message
if (document.getElementById("sweetalert-success"))
    document.getElementById("sweetalert-success").addEventListener("click", function () {
        Swal.fire({
            title: 'Bagus!',
            text: 'Anda menekan tombolnya.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            cancelButtonClass: 'btn btn-danger w-xs mt-2',
            showCloseButton: false
        })
    });

//error Message
if (document.getElementById("sweetalert-error"))
    document.getElementById("sweetalert-error").addEventListener("click", function () {
        Swal.fire({
            title: 'Ups...',
            text: 'Terjadi kesalahan.',
            icon: 'error',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            footer: '<a href="">Kenapa ini bisa terjadi?</a>',
            showCloseButton: false
        })
    });


//info Message
if (document.getElementById("sweetalert-info"))
    document.getElementById("sweetalert-info").addEventListener("click", function () {
        Swal.fire({
            title: 'Ups...',
            text: 'Terjadi kesalahan.',
            icon: 'info',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            footer: '<a href="">Kenapa ini bisa terjadi?</a>',
            showCloseButton: false
        })
    });

//Warning Message
if (document.getElementById("sweetalert-warning"))
    document.getElementById("sweetalert-warning").addEventListener("click", function () {
        Swal.fire({
            title: 'Ups...',
            text: 'Terjadi kesalahan.',
            icon: 'warning',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            footer: '<a href="">Kenapa ini bisa terjadi?</a>',
            showCloseButton: false
        })
    });

// long content
if (document.getElementById("sweetalert-longcontent"))
    document.getElementById("sweetalert-longcontent").addEventListener("click", function () {
        Swal.fire({
            imageUrl: 'https://placeholder.pics/svg/300x1500',
            imageHeight: 1500,
            imageAlt: 'Gambar tinggi',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            showCloseButton: false
        })
    });


//Parameter
if (document.getElementById("sweetalert-params"))
    document.getElementById("sweetalert-params").addEventListener("click", function () {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Tindakan ini tidak dapat dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Tidak, batalkan!',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            cancelButtonClass: 'btn btn-danger w-xs mt-2',
            showCloseButton: false
        }).then(function (result) {
            if (result.value) {
                Swal.fire({
                    title: 'Terhapus!',
                    text: 'File Anda sudah dihapus.',
                    icon: 'success',
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Ya, hapus!",
                })
            } else if (
                // Read more about handling dismissals
                result.dismiss === Swal.DismissReason.cancel
            ) {
                Swal.fire({
                    title: 'Dibatalkan',
                    text: 'File Anda aman :)',
                    icon: 'error',
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Ya, hapus!",
                })
            }
        });
    });


//Custom Image
if (document.getElementById("sweetalert-image"))
    document.getElementById("sweetalert-image").addEventListener("click", function () {
        Swal.fire({
            title: 'Mantap!',
            text: 'Modal dengan gambar kustom.',
            imageUrl: 'assets/images/logo-sm.png',
            imageHeight: 40,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, hapus!",
            animation: false,
            showCloseButton: false
        })
    });
