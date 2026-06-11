@props(['title' => null])

@php
    $themeFile = storage_path('app/site_theme.txt');
    $theme = file_exists($themeFile) ? trim(file_get_contents($themeFile)) : 'emerald';
    $appearance = 'system';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $theme }}"
    data-appearance="{{ $appearance }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}{{ config('app.name', 'Akreditasi') }}</title>

    @php
        $faviconFile = storage_path('app/site_logo.txt');
        $faviconPath = file_exists($faviconFile) ? trim(file_get_contents($faviconFile)) : null;
    @endphp
    @if ($faviconPath)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($faviconPath) }}"
            type="image/png">
        <link rel="apple-touch-icon"
            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($faviconPath) }}">
    @else
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @endif

    <script>
        (function() {
            const html = document.documentElement;
            const mode = html.dataset.appearance || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const shouldDark = mode === 'dark' || (mode === 'system' && prefersDark);
            html.classList.toggle('dark', shouldDark);
        })();
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-surface text-ink-800 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center p-4 md:p-8">
        <div
            class="w-full max-w-5xl bg-white rounded-[2rem] shadow-soft overflow-hidden grid grid-cols-1 lg:grid-cols-2 min-h-[640px]">
            {{-- Left panel: brand --}}
            <div class="relative hidden lg:flex flex-col justify-between p-10 bg-brand-600 text-white overflow-hidden">
                <div class="absolute -top-16 -right-20 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-10 w-72 h-72 rounded-full bg-black/20 blur-3xl"></div>

                <div class="relative flex items-center gap-3">
                    @php
                        $logoFile = storage_path('app/site_logo.txt');
                        $guestLogo = file_exists($logoFile) ? trim(file_get_contents($logoFile)) : null;
                    @endphp
                    @if ($guestLogo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($guestLogo) }}"
                            alt="Logo" class="w-10 h-10 rounded-xl object-cover">
                    @else
                        <div
                            class="w-10 h-10 rounded-xl bg-white text-brand-500 flex items-center justify-center font-bold text-lg">
                            AA</div>
                    @endif
                    <span class="font-bold text-lg tracking-tight">{{ config('app.name', 'Akreditasi') }}</span>
                </div>

                <div class="relative space-y-6">
                    <h2 class="text-3xl font-bold leading-tight">Sistem Akreditasi &amp; Adiwiyata Madrasah/Sekolah</h2>
                    <p class="text-white/80 text-sm leading-relaxed">Kelola siklus akreditasi dan adiwiyata, unggah
                        bukti, isi indikator penilaian, dan pantau progres secara real-time.</p>

                    <div class="flex items-center gap-3 pt-4">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-white/40"></div>
                            <div class="w-8 h-8 rounded-full bg-white/30 border-2 border-white/40"></div>
                            <div class="w-8 h-8 rounded-full bg-white/40 border-2 border-white/40"></div>
                        </div>
                        <p class="text-xs text-white/80">Digunakan oleh tim madrasah/sekolah untuk persiapan
                            akreditasi dan adiwiyata.</p>
                    </div>
                </div>

                <div class="relative text-xs text-white/70">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Akreditasi &amp; Adiwiyata Madrasah/Sekolah.
                </div>
            </div>

            {{-- Right panel: form slot --}}
            <div class="p-8 md:p-12 flex flex-col justify-center">
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    @php
                        $logoFile = storage_path('app/site_logo.txt');
                        $mobileLogo = file_exists($logoFile) ? trim(file_get_contents($logoFile)) : null;
                    @endphp
                    @if ($mobileLogo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mobileLogo) }}"
                            alt="Logo" class="w-10 h-10 rounded-xl object-cover">
                    @else
                        <div
                            class="w-10 h-10 rounded-xl bg-ink-900 text-white flex items-center justify-center font-bold text-lg">
                            AA</div>
                    @endif
                    <span
                        class="font-bold text-lg tracking-tight text-ink-900">{{ config('app.name', 'Akreditasi') }}</span>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>
