@php
    $user = auth()->user();
    $currentAvatar = $avatar && method_exists($avatar, 'temporaryUrl') ? $avatar->temporaryUrl() : null;
    $existingAvatar = $removeAvatar ? null : $user->avatar_url;
@endphp

<div class="space-y-10">
    {{-- Profile section --}}
    <section>
        <header class="mb-6">
            <h3 class="text-lg font-semibold text-ink-900">Profile information</h3>
            <p class="text-sm text-ink-500">Update your name, email address, and avatar.</p>
        </header>

        @if (session('profile_status'))
            <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
                {{ session('profile_status') }}
            </div>
        @endif

        <form wire:submit="updateProfile" class="space-y-6">
            {{-- Avatar --}}
            <div class="flex items-center gap-5">
                <div
                    class="w-20 h-20 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-2xl font-bold overflow-hidden shrink-0">
                    @if ($currentAvatar)
                        <img src="{{ $currentAvatar }}" alt="Avatar preview" class="w-full h-full object-cover">
                    @elseif ($existingAvatar)
                        <img src="{{ $existingAvatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        {{ $user->initials() ?: 'A' }}
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="btn-ghost cursor-pointer">
                            <x-admin.icon name="plus" class="w-4 h-4" />
                            <span>Upload photo</span>
                            <input type="file" wire:model="avatar" accept="image/*" class="hidden">
                        </label>

                        @if ($existingAvatar || $currentAvatar)
                            <button type="button" wire:click="clearAvatar"
                                class="text-sm text-ink-500 hover:text-red-500 font-medium">
                                Remove
                            </button>
                        @endif
                    </div>
                    <p class="text-xs text-ink-500">PNG, JPG, or GIF, up to 2MB.</p>
                    @error('avatar')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <div wire:loading wire:target="avatar" class="text-xs text-ink-500">Uploading…</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-medium text-ink-500 mb-2 uppercase tracking-wide">
                        Name
                    </label>
                    <input id="name" type="text" wire:model="name" class="chip-input" required>
                    @error('name')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-ink-500 mb-2 uppercase tracking-wide">
                        Email
                    </label>
                    <input id="email" type="email" wire:model="email" class="chip-input" required>
                    @error('email')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="updateProfile,avatar">
                    <span wire:loading.remove wire:target="updateProfile">Save changes</span>
                    <span wire:loading wire:target="updateProfile">Saving…</span>
                </button>
            </div>
        </form>
    </section>

    <hr class="border-ink-100">

    {{-- Password section --}}
    <section>
        <header class="mb-6">
            <h3 class="text-lg font-semibold text-ink-900">Change password</h3>
            <p class="text-sm text-ink-500">Leave blank to keep your current password.</p>
        </header>

        @if (session('password_status'))
            <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
                {{ session('password_status') }}
            </div>
        @endif

        <form wire:submit="updatePassword" class="space-y-5">
            <div>
                <label for="current_password"
                    class="block text-xs font-medium text-ink-500 mb-2 uppercase tracking-wide">
                    Current password
                </label>
                <input id="current_password" type="password" wire:model="current_password"
                    autocomplete="current-password" class="chip-input">
                @error('current_password')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="password"
                        class="block text-xs font-medium text-ink-500 mb-2 uppercase tracking-wide">
                        New password
                    </label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password"
                        class="chip-input">
                    @error('password')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation"
                        class="block text-xs font-medium text-ink-500 mb-2 uppercase tracking-wide">
                        Confirm password
                    </label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation"
                        autocomplete="new-password" class="chip-input">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="updatePassword">
                    <span wire:loading.remove wire:target="updatePassword">Update password</span>
                    <span wire:loading wire:target="updatePassword">Updating…</span>
                </button>
            </div>
        </form>
    </section>
</div>