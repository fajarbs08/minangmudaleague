@extends('layouts.auth', ['title' => 'Log in'])

@section('css')
<style>
    .auth-logo-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .auth-logo-link img {
        display: block;
        width: 220px;
        max-width: 100%;
        height: auto;
    }

    .auth-password-toggle {
        background: transparent;
        border: 0;
        color: var(--bs-secondary-color);
        padding: 0;
    }

    .auth-password-toggle:hover,
    .auth-password-toggle:focus {
        color: var(--bs-emphasis-color);
    }

    .auth-input-icon,
    .auth-password-toggle svg,
    .auth-submit-icon {
        width: 1.125rem;
        height: 1.125rem;
        stroke: currentColor;
        stroke-width: 1.8;
        fill: none;
    }

    .auth-password-toggle svg,
    .auth-submit-icon {
        width: 1.05rem;
        height: 1.05rem;
    }
</style>
@endsection

@section('content')
<div class="col-xl-5">
     <div class="card auth-card">
          <div class="card-body">
               <div class="p-3">
                    <div class="mx-auto mb-5 auth-logo text-center">
                         <a class="auth-logo-link" href="{{ route('public.home') }}">
                              <img alt="{{ config('app.name') }}" class="img-fluid" src="/images/logo-full-transparent.png" loading="eager" decoding="async" />
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
                                        <input class="form-control form-control-lg rounded" type="email" name="email" id="emailaddress" value="{{ old('email') }}" required="" autocomplete="username" placeholder="Enter Your Email"/>
                                        <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0" aria-hidden="true">
                                             <svg class="auth-input-icon" viewBox="0 0 24 24">
                                                  <path d="M4 6h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"></path>
                                                  <path d="m22 8-8.97 5.7a2 2 0 0 1-2.06 0L2 8"></path>
                                             </svg>
                                        </p>
                                   </div>
                              </div>
                              <div class="mb-4">
                                   <label class="form-label" for="password">Password</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded"  type="password" required="" id="password" name="password" autocomplete="current-password" placeholder="Enter your password"/>
                                        <button class="auth-password-toggle position-absolute end-0 top-50 translate-middle-y me-3" type="button" id="toggle-password" aria-label="Tampilkan password" aria-pressed="false">
                                             <span id="toggle-password-icon" aria-hidden="true">
                                                  <svg viewBox="0 0 24 24">
                                                       <path d="M2.06 12.34a1 1 0 0 1 0-.68 10 10 0 0 1 19.88 0 1 1 0 0 1 0 .68 10 10 0 0 1-19.88 0"></path>
                                                       <circle cx="12" cy="12" r="3"></circle>
                                                  </svg>
                                             </span>
                                        </button>
                                   </div>
                              </div>
                              <div class="mb-3">
                                   <div class="form-check">
                                        <input class="form-check-input" id="checkbox-signin" name="remember" type="checkbox" value="1" @checked(old('remember')) />
                                        <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                   </div>
                              </div>
                              <div class="text-center d-grid">
                                   <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit">
                                        <svg class="auth-submit-icon" viewBox="0 0 24 24" aria-hidden="true">
                                             <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                             <path d="M10 17l5-5-5-5"></path>
                                             <path d="M15 12H3"></path>
                                        </svg>
                                        Sign In
                                   </button>
                              </div>
                              <div class="text-center mt-3">
                                   <a class="text-muted text-decoration-none" href="{{ route('public.home') }}">
                                        &larr; Kembali ke beranda
                                   </a>
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

    const eyeIcon = '<svg viewBox="0 0 24 24"><path d="M2.06 12.34a1 1 0 0 1 0-.68 10 10 0 0 1 19.88 0 1 1 0 0 1 0 .68 10 10 0 0 1-19.88 0"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    const eyeOffIcon = '<svg viewBox="0 0 24 24"><path d="m3 3 18 18"></path><path d="M10.58 10.58a2 2 0 1 0 2.83 2.83"></path><path d="M9.88 5.09A9.8 9.8 0 0 1 12 4.87c4.84 0 8.8 3.04 9.94 7.13a1 1 0 0 1 0 .68 10 10 0 0 1-3.3 4.83"></path><path d="M6.61 6.61A10 10 0 0 0 2.06 11.66a1 1 0 0 0 0 .68 10 10 0 0 0 12.78 6.16"></path></svg>';

    if (!passwordInput || !toggleButton || !toggleIcon) {
        return;
    }

    toggleButton.addEventListener('click', function () {
        const isHidden = passwordInput.type === 'password';

        passwordInput.type = isHidden ? 'text' : 'password';
        toggleButton.setAttribute('aria-pressed', String(isHidden));
        toggleButton.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        toggleIcon.innerHTML = isHidden ? eyeOffIcon : eyeIcon;
    });
});
</script>
@endsection
