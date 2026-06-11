@props(['title' => null])

@php
    $tabs = [
        ['route' => 'settings.profile', 'label' => 'Profile', 'icon' => 'user'],
        ['route' => 'settings.appearance', 'label' => 'Appearance', 'icon' => 'eye'],
        ['route' => 'settings.theme', 'label' => 'Theme', 'icon' => 'star'],
    ];
@endphp

<x-layouts.app :title="$title">
    <div class="max-w-4xl mx-auto w-full">
        <div class="mb-6">
            <h2 class="text-2xl sm:text-3xl font-bold text-ink-900">Settings</h2>
            <p class="text-ink-500 text-sm mt-1">Manage your profile, appearance, and theme preferences.</p>
        </div>

        <nav class="flex gap-1 bg-ink-100 rounded-full p-1 mb-6 overflow-x-auto no-scrollbar">
            @foreach ($tabs as $tab)
                @php $isActive = request()->routeIs($tab['route']); @endphp
                <a href="{{ route($tab['route']) }}" wire:navigate
                    class="shrink-0 px-4 py-2 text-sm font-medium rounded-full flex items-center gap-2 transition-colors {{ $isActive ? 'bg-white text-ink-900 shadow-soft' : 'text-ink-600 hover:text-ink-900' }}">
                    <x-admin.icon :name="$tab['icon']" class="w-4 h-4" />
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="bg-white rounded-[1.5rem] shadow-soft p-6 sm:p-8">
            {{ $slot }}
        </div>
    </div>
</x-layouts.app>