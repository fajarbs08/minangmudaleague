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
        @stack('scripts')

    </body>

</html>
