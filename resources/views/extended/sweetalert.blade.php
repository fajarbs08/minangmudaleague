@extends('layouts.vertical', ['title' => 'Sweetalert'])

@section('content')
<div class="card">
     <div class="card-body">
          <div class="row g-5">
               <div class="col-lg-12">
                    <h5 class="card-title mb-4">
                         Dasar
                    </h5>
                    <button class="btn btn-primary" id="sweetalert-basic" type="button">Klik saya</button>
               </div>
               <div class="col-lg-12">
                    <h5 class="card-title mb-4">
                         Judul dengan Teks di Bawah
                    </h5>
                    <button class="btn btn-primary" id="sweetalert-title" type="button">Klik saya</button>
               </div>
               <div class="col-lg-12">
                    <h5 class="card-title mb-4">
                         Pesan
                    </h5>
                    <div class="hstack gap-2">
                         <button class="btn btn-success" id="sweetalert-success" type="button">Sukses</button>
                         <button class="btn btn-warning" id="sweetalert-warning" type="button">Peringatan</button>
                         <button class="btn btn-info" id="sweetalert-info" type="button">Info</button>
                         <button class="btn btn-danger" id="sweetalert-error" type="button">Error</button>
                    </div>
               </div>
               <div class="col-lg-12">
                    <h5 class="card-title mb-4">
                         Pesan dengan Gambar Panjang
                    </h5>
                    <button class="btn btn-primary" id="sweetalert-longcontent" type="button">Klik saya</button>
               </div>
               <div class="col-lg-12">
                    <h5 class="card-title mb-4">
                         Parameter
                    </h5>
                    <button class="btn btn-primary" id="sweetalert-params" type="button">Klik saya</button>
               </div> <!-- end col -->
          </div> <!-- end row -->
     </div>
</div>
@endsection

@section('scripts')
@vite(['resources/js/components/extended-sweetalert.js'])
@endsection
