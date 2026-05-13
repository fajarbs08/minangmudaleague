<!DOCTYPE html>
<html lang="en">

    <head>
        @include('layouts.partials.title-meta', ['title' => $title])

        @include('layouts.partials.head-css')
    </head>

    <body>
        @include('partials.preloader')
        
        <div class="wrapper">

            @include('layouts.partials.main-nav')
            @include('layouts.partials.topbar')

            <div class="page-container">
                <div class="page-content">

                    @include('layouts.partials.season-context-banner')

                    @yield('content')

                </div>
                @include('layouts.partials.footer')
            </div>

        </div>

        @include('layouts.partials.club-onboarding-modal')

        @include('layouts.partials.vendor-scripts')
        <script>
            (function () {
                if (!('serviceWorker' in navigator)) {
                    return;
                }

                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function () {
                        // PWA support is progressive; ignore registration failures.
                    });
                });
            })();
        </script>
        @stack('scripts')

    </body>

</html>
