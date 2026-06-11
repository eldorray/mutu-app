@php
    $user = auth()->user();
    $activeModule = session('active_module', 'akreditasi');
    $moduleNav = [
        'akreditasi' => ['route' => 'accreditation.index', 'label' => 'Akreditasi', 'icon' => 'clipboard'],
        'adiwiyata' => ['route' => 'adiwiyata.index', 'label' => 'Adiwiyata', 'icon' => 'leaf'],
    ];
    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        $moduleNav[$activeModule] ?? $moduleNav['akreditasi'],
        ['route' => 'settings.profile', 'label' => 'Settings', 'icon' => 'settings'],
    ];
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-ink-100 flex flex-col py-6 gap-6 transition-transform duration-200 ease-out
           lg:sticky lg:top-0 lg:h-screen lg:w-20 lg:translate-x-0 lg:z-10 lg:items-center lg:gap-8 shrink-0">
    {{-- Top: logo + close --}}
    <div class="flex items-center justify-between px-5 lg:px-0 lg:justify-center">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            @php
                $logoFile = storage_path('app/site_logo.txt');
                $siteLogo = file_exists($logoFile) ? trim(file_get_contents($logoFile)) : null;
            @endphp
            @if ($siteLogo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($siteLogo) }}" alt="Logo"
                    class="w-10 h-10 rounded-xl object-cover shrink-0">
            @else
                <span
                    class="w-10 h-10 bg-ink-900 rounded-xl flex items-center justify-center text-white font-bold text-lg leading-none shrink-0">
                    {{ $activeModule === 'adiwiyata' ? 'AD' : 'IA' }}
                </span>
            @endif
            <span class="font-bold text-ink-900 tracking-tight lg:hidden">{{ $moduleNav[$activeModule]['label'] ?? 'Akreditasi' }}</span>
        </a>

        {{-- Close button (mobile only) --}}
        <button type="button" @click="sidebarOpen = false"
            class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-ink-50 transition-colors text-ink-500 lg:hidden"
            aria-label="Close menu">
            <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg">
                <line x1="18" x2="6" y1="6" y2="18" />
                <line x1="6" x2="18" y1="6" y2="18" />
            </svg>
        </button>
    </div>

    {{-- User summary on mobile --}}
    @if ($user)
        <div class="px-5 lg:hidden">
            <div class="flex items-center gap-3 p-3 rounded-2xl bg-ink-50">
                <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-sm font-semibold shrink-0 overflow-hidden">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        {{ $user->initials() ?: 'A' }}
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink-900 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-ink-500 truncate">{{ $user->email }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Nav --}}
    <nav class="flex flex-col gap-1 px-3 lg:px-0 lg:gap-2 lg:bg-ink-50 lg:rounded-full lg:p-2">
        @foreach ($nav as $item)
            @php
                $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*');
                $hasRoute = Route::has($item['route']);
            @endphp
            <a href="{{ $hasRoute ? route($item['route']) : '#' }}" title="{{ $item['label'] }}"
                @click="sidebarOpen = false" wire:navigate
                class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition-colors
                       lg:w-10 lg:h-10 lg:px-0 lg:py-0 lg:rounded-full lg:justify-center lg:gap-0
                       {{ $isActive ? 'bg-ink-50 text-ink-900 lg:bg-white lg:shadow-soft' : 'text-ink-600 hover:bg-ink-50 lg:text-ink-500 lg:hover:bg-ink-100' }}">
                <x-admin.icon :name="$item['icon']" class="w-[18px] h-[18px] shrink-0" />
                <span class="lg:hidden">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Logout at the bottom --}}
    <form method="POST" action="{{ route('logout') }}" class="mt-auto mb-2 px-3 lg:px-0">
        @csrf
        <button type="submit"
            class="flex items-center gap-3 w-full rounded-2xl px-3 py-2.5 text-sm font-medium text-ink-600 hover:bg-ink-50 transition-colors
                   lg:w-10 lg:h-10 lg:px-0 lg:py-0 lg:rounded-full lg:justify-center lg:gap-0 lg:text-ink-500"
            title="Logout">
            <svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg" class="shrink-0">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" x2="9" y1="12" y2="12" />
            </svg>
            <span class="lg:hidden">Log out</span>
        </button>
    </form>
</aside>
