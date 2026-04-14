@extends('layouts.auth', ['title' => 'Log in'])

@section('content')
<div class="col-xl-5">
     <div class="card auth-card">
          <div class="card-body">
               <div class="p-3">
                    <div class="mx-auto mb-5 auth-logo text-center">
                         <a class="logo-dark" href="{{ route('login') }}">
                              <img alt="{{ config('app.name') }}" class="img-fluid" src="/images/logo-full-transparent.png" style="width: 220px; height: auto;" />
                         </a>
                         <a class="logo-light" href="{{ route('login') }}">
                              <img alt="{{ config('app.name') }}" class="img-fluid" src="/images/logo-full-transparent.png" style="width: 220px; height: auto;" />
                         </a>
                    </div>
                    <div class="text-center">
                         <h3 class="fw-bold text-dark fs-20">{{ config('app.name') }}</h3>
                         <p class="text-muted mt-1 mb-4">Masuk untuk mengakses sistem administrasi liga.</p>
                    </div>
                    <div class="p-3">
                         <form method="POST" action="{{ route('login')}}" class="authentication-form">
                              @csrf
                              @if (sizeof($errors) > 0)
                              @foreach ($errors->all() as $error)
                              <p class="text-danger mb-3">{{ $error }}</p>
                              @endforeach
                              @endif

                              <div class="mb-4">
                                   <label class="form-label" for="emailaddress">Email</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded" type="email" name="email" id="emailaddress" value="test@example.com" required="" placeholder="Enter Your Email"/>
                                        <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                                             <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:letter-bold-duotone"></iconify-icon>
                                        </p>
                                   </div>
                              </div>
                              <div class="mb-4">
                                   <label class="form-label" for="password">Password</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded"  type="password" required="" id="password" name="password" value="password" placeholder="Enter your password"/>
                                        <button class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2" type="button" id="toggle-password" aria-label="Show password" aria-pressed="false">
                                             <iconify-icon class="fs-20 mt-1 text-muted" id="toggle-password-icon" icon="solar:eye-bold-duotone"></iconify-icon>
                                        </button>
                                   </div>
                              </div>
                              <div class="mb-3">
                                   <div class="form-check">
                                        <input class="form-check-input" id="checkbox-signin" type="checkbox" />
                                        <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                   </div>
                              </div>
                              <div class="text-center d-grid">
                                   <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit"><i class="fs-18" data-lucide="log-in"></i> Sign In</button>
                              </div>
                         </form>
                    </div>
               </div> <!-- end col -->
          </div> <!-- end card-body -->
     </div> <!-- end card -->
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('toggle-password');
    const toggleIcon = document.getElementById('toggle-password-icon');

    if (!passwordInput || !toggleButton || !toggleIcon) {
        return;
    }

    toggleButton.addEventListener('click', function () {
        const isHidden = passwordInput.type === 'password';

        passwordInput.type = isHidden ? 'text' : 'password';
        toggleButton.setAttribute('aria-pressed', String(isHidden));
        toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        toggleIcon.setAttribute('icon', isHidden ? 'solar:eye-closed-bold-duotone' : 'solar:eye-bold-duotone');
    });
});
</script>
@endsection
