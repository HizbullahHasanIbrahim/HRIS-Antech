<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Antech Async HRIS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=jetbrains-mono:400,500" rel="stylesheet" />

        @vite('resources/js/app.ts')
        @inertiaHead

        <!-- Script Anti-FOUC: Apply theme based on localStorage or system preference -->
        <script>
            (function() {
                const theme = localStorage.getItem('hris-theme') || 'system';
                const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                const appliedTheme = theme === 'system' ? systemTheme : theme;

                // Terapkan class pada <html> untuk menghindari flash
                if (appliedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                // Listen for perubahan preferensi sistem (optional)
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    const saved = localStorage.getItem('hris-theme');
                    if (saved && saved !== 'system') return;
                    // apply tema system baru
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                });
            })();
        </script>
    </head>
    <body class="font-sans antialiased">
        @routes
        @inertia
    </body>
</html>
