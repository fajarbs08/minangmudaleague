@php
    $showClubOnboarding = session('show_club_onboarding') && auth()->check() && auth()->user()->isClubUser();
@endphp

@if ($showClubOnboarding)
    <div
        class="modal fade"
        id="clubOnboardingModal"
        tabindex="-1"
        aria-labelledby="clubOnboardingModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <span class="badge bg-primary-subtle text-primary mb-2">Flow Informasi</span>
                        <h5 class="modal-title mb-1" id="clubOnboardingModalLabel">Alur Registrasi Akun Club</h5>
                        <p class="text-muted mb-0">Ikuti urutan ini agar proses verifikasi dan penyusunan DSP berjalan rapi.</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="badge rounded-pill bg-primary text-white">1</span>
                                    <h6 class="mb-0">Input Data Klub</h6>
                                </div>
                                <p class="text-muted small mb-3">Lengkapi profil klub, logo, alamat, dan surat pernyataan terlebih dahulu.</p>
                                <a href="{{ route('clubs.index') }}" class="btn btn-sm btn-outline-primary">Buka Data Klub</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="badge rounded-pill bg-success text-white">2</span>
                                    <h6 class="mb-0">Input Data Pemain</h6>
                                </div>
                                <p class="text-muted small mb-3">Tambahkan pemain beserta dokumen pendukung sesuai kelompok usia yang diikuti.</p>
                                <a href="{{ route('players.index') }}" class="btn btn-sm btn-outline-success">Buka Data Pemain</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="badge rounded-pill bg-info text-white">3</span>
                                    <h6 class="mb-0">Input Data Official</h6>
                                </div>
                                <p class="text-muted small mb-3">Masukkan pelatih, manajer, dan official lain berikut identitas atau lisensinya.</p>
                                <a href="{{ route('officials.index') }}" class="btn btn-sm btn-outline-info">Buka Data Official</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="badge rounded-pill bg-warning text-dark">4</span>
                                    <h6 class="mb-0">Susun DSP</h6>
                                </div>
                                <p class="text-muted small mb-3">Setelah data inti siap, susun daftar susunan pemain sesuai kebutuhan pertandingan.</p>
                                <a href="{{ route('lineup-lists.index') }}" class="btn btn-sm btn-outline-warning">Buka DSP</a>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-primary-subtle border-primary-subtle mt-4 mb-0">
                        Pastikan setiap data sudah lengkap sebelum menunggu verifikasi admin. Jika ada catatan revisi, perbaiki pada menu yang sesuai lalu submit ulang.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Saya mengerti</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalEl = document.getElementById('clubOnboardingModal');
                if (!modalEl || typeof bootstrap === 'undefined') {
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        </script>
    @endpush
@endif
