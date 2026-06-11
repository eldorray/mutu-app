<div class="w-full max-w-md mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-ink-900 mb-2">Masuk</h1>
        <p class="text-sm text-ink-500">Masuk ke akun Anda, lalu pilih modul Akreditasi atau Adiwiyata.</p>
    </div>

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="module"
                class="block text-xs font-medium text-ink-500 mb-2 uppercase tracking-wide">Modul</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-4 flex items-center text-ink-400">
                    <x-admin.icon name="clipboard" class="w-4 h-4" />
                </span>
                <select id="module" wire:model="module" class="chip-input pl-11">
                    <option value="akreditasi">Akreditasi</option>
                    <option value="adiwiyata">Adiwiyata</option>
                </select>
            </div>
            @error('module')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email"
                class="block text-xs font-medium text-ink-500 mb-2 uppercase tracking-wide">Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-4 flex items-center text-ink-400">
                    <x-admin.icon name="mail" class="w-4 h-4" />
                </span>
                <input id="email" type="email" wire:model="email" autocomplete="email" required
                    class="chip-input pl-11" placeholder="email@contoh.com">
            </div>
            @error('email')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password"
                    class="block text-xs font-medium text-ink-500 uppercase tracking-wide">Password</label>
            </div>
            <div class="relative" x-data="{ show: false }">
                <span class="absolute inset-y-0 left-4 flex items-center text-ink-400">
                    <x-admin.icon name="lock" class="w-4 h-4" />
                </span>
                <input id="password" :type="show ? 'text' : 'password'" wire:model="password"
                    autocomplete="current-password" required class="chip-input pl-11 pr-11" placeholder="••••••••">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-4 flex items-center text-ink-400 hover:text-ink-600"
                    aria-label="Tampilkan password">
                    <template x-if="!show"><x-admin.icon name="eye" class="w-4 h-4" /></template>
                    <template x-if="show"><x-admin.icon name="eye-off" class="w-4 h-4" /></template>
                </button>
            </div>
            @error('password')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-ink-600 select-none">
            <input type="checkbox" wire:model="remember"
                class="rounded border-ink-300 text-brand-500 focus:ring-brand-500/30">
            <span>Ingat saya</span>
        </label>

        <button type="submit" class="btn-primary w-full py-3" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memproses...</span>
        </button>
    </form>

    <p class="mt-8 text-sm text-ink-500 text-center">
        Belum punya akun?
        <a href="{{ route('register') }}" wire:navigate
            class="text-brand-500 hover:text-brand-600 font-medium">Daftar</a>
    </p>
</div>
