@extends('layouts.vertical', ['title' => 'Toastify'])

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row g-5">
            <div class="col-lg-12">
                <h5 class="card-title mb-4">
                    Contoh Toastify Dasar
                </h5>
                <div class="hstack flex-wrap gap-2">
                    <button class="btn btn-light w-xs" data-toast="" data-toast-classname="primary"
                        data-toast-close="close" data-toast-duration="3000" data-toast-gravity="top"
                        data-toast-position="right" data-toast-style="style"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Default
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-classname="success"
                        data-toast-duration="3000" data-toast-gravity="top" data-toast-position="center"
                        data-toast-text="Pengajuan Anda berhasil dikirim." type="button">
                        Sukses
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-classname="warning"
                        data-toast-duration="3000" data-toast-gravity="top" data-toast-position="center"
                        data-toast-text="Peringatan! Terjadi kesalahan, silakan coba lagi." type="button">
                        Peringatan
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-classname="danger"
                        data-toast-duration="3000" data-toast-gravity="top" data-toast-position="center"
                        data-toast-text="Error! Terjadi kesalahan." type="button">
                        Error
                    </button>
                </div>
            </div>
            <div class="col-lg-12">
                <h5 class="card-title mb-4">
                    Contoh Posisi Tampil
                </h5>
                <div class="hstack flex-wrap gap-2">
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-gravity="top" data-toast-position="left"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Atas Kiri
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-gravity="top" data-toast-position="center"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Atas Tengah
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-gravity="top" data-toast-position="right"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Atas Kanan
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-gravity="bottom" data-toast-position="left"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Bawah Kiri
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-gravity="bottom" data-toast-position="center"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Bawah Tengah
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-gravity="bottom" data-toast-position="right"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Bawah Kanan
                    </button>
                </div>
            </div>
            <div class="col-lg-12">
                <h5 class="card-title mb-4">
                    Contoh Offset, Tombol Tutup, dan Durasi
                </h5>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-gravity="top" data-toast-offset="" data-toast-position="right"
                        data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast." type="button">
                        Posisi Offset
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-close="close" data-toast-duration="3000"
                        data-toast-position="right" data-toast-text="Selamat datang kembali! Ini adalah notifikasi toast."
                        type="button">
                        Tampilkan tombol tutup
                    </button>
                    <button class="btn btn-light w-xs" data-toast="" data-toast-duration="5000" data-toast-gravity="top"
                        data-toast-position="right" data-toast-text="Durasi toast 5 detik" type="button">
                        Durasi
                    </button>
                </div>
            </div><!-- end col -->
        </div> <!-- end row -->
    </div>
</div>
@endsection
