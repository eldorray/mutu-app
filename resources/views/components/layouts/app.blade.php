@props(['title' => null])

@php
    $user = auth()->user();
    $appearance = $user?->appearance ?? 'system';
    $theme = $user?->color_theme ?? 'emerald';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="{{ $theme }}"
    data-appearance="{{ $appearance }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}{{ config('app.name', 'Laravel') }}</title>

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

    {{-- Apply appearance class early to avoid FOUC + listen for Livewire-driven changes --}}
    <script>
        (function() {
            const html = document.documentElement;

            function applyAppearance(mode) {
                html.dataset.appearance = mode;
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const shouldDark = mode === 'dark' || (mode === 'system' && prefersDark);
                html.classList.toggle('dark', shouldDark);
            }

            function applyTheme(theme) {
                html.dataset.theme = theme;
            }

            // Initial apply (before paint)
            applyAppearance(html.dataset.appearance || 'system');

            // React to system changes when appearance is "system"
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    if (html.dataset.appearance === 'system') {
                        html.classList.toggle('dark', e.matches);
                    }
                });
            }

            // Listen for Livewire-dispatched events to apply instantly
            document.addEventListener('livewire:init', () => {
                Livewire.on('appearance-changed', ({
                    value
                }) => applyAppearance(value));
                Livewire.on('theme-changed', ({
                    value
                }) => applyTheme(value));
            });
        })();
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full bg-surface-soft text-ink-800 font-sans antialiased overflow-hidden" x-data="{ sidebarOpen: false }">
    <div class="h-screen flex">
        {{-- Sidebar (static on lg+, slide-over on mobile) --}}
        <x-admin.sidebar />

        {{-- Backdrop for mobile drawer --}}
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 bg-ink-900/40 backdrop-blur-sm z-30 lg:hidden" x-cloak></div>

        {{-- Main --}}
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            <x-admin.header :title="$title" />

            <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                @if (session('status'))
                    <div
                        class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>

</html>
