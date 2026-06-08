<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-ktn-surface antialiased">
        <div class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-ktn-topo p-6 md:p-10">
            <div class="hero-kontur absolute inset-y-0 -left-10 -right-10 opacity-30 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-ktn-surface/70"></div>

            <div class="relative z-10 flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-ktn-forest" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
