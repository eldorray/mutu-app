@props(['title' => null])

@php
    $user = auth()->user();
    $activeModule = session('active_module', 'akreditasi');
    $moduleNav = [
        'akreditasi' => ['route' => 'accreditation.index', 'label' => 'Akreditasi', 'icon' => 'clipboard'],
        'adiwiyata' => ['route' => 'adiwiyata.index', 'label' => 'Adiwiyata', 'icon' => 'leaf'],
    ];
    $topNav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        $moduleNav[$activeModule] ?? $moduleNav['akreditasi'],
        ['route' => 'settings.profile', 'label' => 'Settings', 'icon' => 'settings'],
    ];
@endphp

<header
    class="h-16 sm:h-20 border-b border-ink-100 flex items-center justify-between px-4 sm:px-6 lg:px-8 bg-white shrink-0 sticky top-0 z-20">
    <div class="flex items-center gap-3 sm:gap-6 min-w-0">
        {{-- Hamburger (mobile) --}}
        <button type="button" @click="sidebarOpen = true"
            class="lg:hidden w-10 h-10 rounded-full flex items-center justify-center text-ink-600 hover:bg-ink-50 transition-colors border border-ink-200 shrink-0"
            aria-label="Open menu">
            <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" viewBox="0 0 24 24" width="18">
                <line x1="3" x2="21" y1="6" y2="6" />
                <line x1="3" x2="21" y1="12" y2="12" />
                <line x1="3" x2="21" y1="18" y2="18" />
            </svg>
        </button>

        @php
            $logoFile = storage_path('app/site_logo.txt');
            $headerLogo = file_exists($logoFile) ? trim(file_get_contents($logoFile)) : null;
        @endphp
        <div class="flex items-center gap-2.5">
            @if ($headerLogo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($headerLogo) }}" alt="Logo"
                    class="w-8 h-8 rounded-lg object-cover shrink-0">
            @endif
            <h1 class="text-lg sm:text-xl font-bold text-ink-900 tracking-tight truncate">
                {{ $moduleNav[$activeModule]['label'] ?? 'Akreditasi' }}</h1>
        </div>

        <nav class="hidden md:flex items-center gap-1 bg-ink-50 rounded-full p-1">
            @foreach ($topNav as $item)
                @php $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}" wire:navigate
                    class="px-3 lg:px-4 py-2 text-sm font-medium rounded-full flex items-center gap-2 transition-colors {{ $isActive ? 'bg-white text-ink-900 shadow-soft' : 'text-ink-500 hover:text-ink-900' }}">
                    <x-admin.icon :name="$item['icon']" class="w-4 h-4" />
                    <span class="hidden lg:inline">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        {{-- Module switcher --}}
        <div x-data="{ open: false }" @click.outside="open = false" class="relative" x-cloak>
            <button type="button" @click="open = !open"
                class="flex items-center gap-2 pl-3 pr-2 py-2 rounded-full border border-ink-200 hover:bg-ink-50 transition-colors text-sm font-medium text-ink-700">
                <x-admin.icon :name="$moduleNav[$activeModule]['icon'] ?? 'clipboard'" class="w-4 h-4 text-ink-500" />
                <span class="hidden sm:inline">{{ $moduleNav[$activeModule]['label'] ?? 'Akreditasi' }}</span>
                <x-admin.icon name="chevron-down" class="w-3.5 h-3.5 text-ink-400" />
            </button>
            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-soft border border-ink-100 p-2 z-20">
                <p class="px-3 pt-1 pb-2 text-[10px] font-bold uppercase tracking-wide text-ink-400">Ganti Modul</p>
                @foreach ($moduleNav as $key => $mod)
                    <a href="{{ route('module.switch', $key) }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm {{ $activeModule === $key ? 'bg-brand-50 text-brand-700 font-medium' : 'text-ink-700 hover:bg-ink-50' }}">
                        <x-admin.icon :name="$mod['icon']" class="w-4 h-4" />
                        {{ $mod['label'] }}
                        @if ($activeModule === $key)
                            <x-admin.icon name="check-circle" class="w-4 h-4 ml-auto text-brand-600" />
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <button type="button"
            class="hidden sm:flex w-10 h-10 rounded-full items-center justify-center text-ink-500 hover:bg-ink-50 transition-colors border border-ink-200"
            aria-label="Inbox">
            <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" viewBox="0 0 24 24" width="18">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
            </svg>
        </button>

        <button type="button"
            class="w-10 h-10 rounded-full flex items-center justify-center text-ink-500 hover:bg-ink-50 transition-colors border border-ink-200 relative"
            aria-label="Notifications">
            <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" viewBox="0 0 24 24" width="18">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <span class="absolute top-2 right-2 w-2 h-2 bg-brand-500 rounded-full"></span>
        </button>

        <div x-data="{ open: false }" @click.outside="open = false" class="relative" x-cloak>
            <button type="button" @click="open = !open"
                class="flex items-center gap-2 pl-1 pr-2 sm:pr-3 py-1 rounded-full border border-ink-200 hover:bg-ink-50 transition-colors">
                <div
                    class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-sm font-semibold overflow-hidden">
                    @if ($user?->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        {{ $user?->initials() ?: 'A' }}
                    @endif
                </div>
                <span
                    class="hidden md:inline text-sm font-medium text-ink-800 max-w-[120px] truncate">{{ $user?->name }}</span>
            </button>

            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-soft border border-ink-100 p-2 z-20">
                <div class="px-3 py-2 border-b border-ink-100 mb-1">
                    <p class="text-sm font-semibold text-ink-900 truncate">{{ $user?->name }}</p>
                    <p class="text-xs text-ink-500 truncate">{{ $user?->email }}</p>
                </div>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="block px-3 py-2 rounded-xl text-sm text-ink-700 hover:bg-ink-50">Dashboard</a>
                <a href="{{ route('settings.profile') }}" wire:navigate
                    class="block px-3 py-2 rounded-xl text-sm text-ink-700 hover:bg-ink-50">Profile</a>
                <a href="{{ route('settings.appearance') }}" wire:navigate
                    class="block px-3 py-2 rounded-xl text-sm text-ink-700 hover:bg-ink-50">Appearance</a>
                <a href="{{ route('settings.theme') }}" wire:navigate
                    class="block px-3 py-2 rounded-xl text-sm text-ink-700 hover:bg-ink-50">Theme</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-xl text-sm text-brand-600 hover:bg-brand-50">Log
                        out</button>
                </form>
            </div>
        </div>
    </div>
</header>
