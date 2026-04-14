@extends('layouts.vertical', ['title' => 'Alerts'])

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row g-5">
            <div class="col-lg-6">
                <h5 class="card-title mb-4">Contoh Dasar</h5>
                <div class="alert alert-primary" role="alert">
                    Ini contoh alert utama yang sederhana.
                </div>
                <div class="alert alert-secondary" role="alert">
                    Ini contoh alert sekunder yang sederhana.
                </div>
                <div class="alert alert-success" role="alert">
                    Ini contoh alert sukses yang sederhana.
                </div>
                <div class="alert alert-danger" role="alert">
                    Ini contoh alert bahaya yang sederhana.
                </div>
                <div class="alert alert-warning" role="alert">
                    Ini contoh alert peringatan yang sederhana.
                </div>
                <div class="alert alert-info" role="alert">
                    Ini contoh alert info yang sederhana.
                </div>
                <div class="alert alert-light" role="alert">
                    Ini contoh alert light yang sederhana.
                </div>
                <div class="alert alert-dark mb-0" role="alert">
                    Ini contoh alert gelap yang sederhana.
                </div>
            </div>
            <div class="col-lg-6">
                <h5 class="card-title mb-4">
                    Contoh Alert yang Bisa Ditutup
                </h5>
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert utama yang sederhana.
                </div>
                <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert sekunder yang sederhana.
                </div>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert sukses yang sederhana.
                </div>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert bahaya yang sederhana.
                </div>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert peringatan yang sederhana.
                </div>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert info yang sederhana.
                </div>
                <div class="alert alert-light alert-dismissible fade show" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert light yang sederhana.
                </div>
                <div class="alert alert-dark alert-dismissible fade show mb-0" role="alert">
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="alert" type="button"></button>
                    Ini contoh alert gelap yang sederhana.
                </div>
            </div><!-- end col -->
            <div class="col-lg-6">
                <h5 class="card-title mb-4">
                    Contoh Alert dengan Tautan
                </h5>
                <div class="alert alert-primary" role="alert">
                    Ini contoh alert utama dengan <a class="alert-link" href="javascript:void(0);">tautan contoh</a>.
                    Silakan klik jika diperlukan.
                </div>
                <div class="alert alert-secondary" role="alert">
                    Ini contoh alert sekunder dengan <a class="alert-link" href="javascript:void(0);">tautan contoh</a>.
                    Silakan klik jika diperlukan.
                </div>
                <div class="alert alert-success" role="alert">
                    Ini contoh alert sukses dengan <a class="alert-link" href="javascript:void(0);">tautan contoh</a>.
                    Silakan klik jika diperlukan.
                </div>
                <div class="alert alert-danger mb-0" role="alert">
                    Ini contoh alert bahaya dengan <a class="alert-link" href="javascript:void(0);">tautan contoh</a>.
                    Silakan klik jika diperlukan.
                </div>
            </div><!-- end col -->
            <div class="col-lg-6">
                <h5 class="card-title mb-4">
                    Contoh Alert dengan Konten Tambahan
                </h5>
                <div class="row">
                    <div class="col-xl-6">
                        <div class="alert alert-primary mb-3 p-3 mb-xl-0" role="alert">
                            <h4 class="alert-heading">Bagus!</h4>
                            <p class="mb-0">Anda berhasil membaca pesan alert penting ini. Contoh teks ini dibuat lebih
                                panjang agar terlihat bagaimana jarak dan spasi bekerja di dalam alert.</p>
                            <hr />
                            <p class="mb-0">Gunakan utilitas margin jika diperlukan agar tampilan tetap rapi.</p>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="alert alert-secondary p-3 mb-0" role="alert">
                            <h4 class="alert-heading">Bagus!</h4>
                            <p class="mb-0">Anda berhasil membaca pesan alert penting ini. Contoh teks ini dibuat lebih
                                panjang agar terlihat bagaimana jarak dan spasi bekerja di dalam alert.</p>
                            <hr />
                            <p class="mb-0">Gunakan utilitas margin jika diperlukan agar tampilan tetap rapi.</p>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
            <div class="col-lg-6">
                <h5 class="card-title mb-4">
                    Contoh Langsung
                </h5>
                <div id="liveAlertPlaceholder"></div>
                <button class="btn btn-primary" id="liveAlertBtn" type="button">Tampilkan alert</button>
            </div><!-- end col -->
        </div> <!-- end row -->
    </div>
</div>
@endsection
