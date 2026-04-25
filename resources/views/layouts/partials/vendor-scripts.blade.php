<script>
    if (typeof globalThis !== 'undefined' && typeof globalThis.global === 'undefined') {
        globalThis.global = globalThis;
    }
</script>
@vite(['resources/js/app.js', 'resources/js/layout.js'])
@yield('scripts')
