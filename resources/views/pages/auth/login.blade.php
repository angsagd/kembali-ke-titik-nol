<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Masuk ke akun Anda')" :description="__('Masukkan nomor WhatsApp dan password untuk masuk')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- WhatsApp Number -->
            <flux:input
                name="whatsapp_number"
                :label="__('Nomor WhatsApp')"
                :value="old('whatsapp_number')"
                type="tel"
                required
                autofocus
                autocomplete="tel"
                placeholder="6281234567890"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        <flux:text class="text-center">
            {{ __('Akun alumni dibuat oleh panitia. Hubungi admin jika belum memiliki akses.') }}
        </flux:text>
    </div>
</x-layouts::auth>
