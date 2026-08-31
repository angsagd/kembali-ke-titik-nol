<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Portal Alumni')" class="grid">
                    <flux:sidebar.item icon="arrow-left" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                        {{ __('Beranda Publik') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @can('view-alumni-directory')
                    <flux:sidebar.group :heading="__('Alumni')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('alumni.directory.index')" :current="request()->routeIs('alumni.directory.*')" wire:navigate>
                            {{ __('Direktori Alumni') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="photo" :href="route('alumni.then-now.index')" :current="request()->routeIs('alumni.then-now.*')" wire:navigate>
                            {{ __('Dulu & Sekarang') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Perjalanan')" class="grid">
                        <flux:sidebar.item icon="map" :href="route('alumni.distribution.index')" :current="request()->routeIs('alumni.distribution.*')" wire:navigate>
                            {{ __('Peta Alumni') }}
                        </flux:sidebar.item>

                        @can('update-own-alumni-profile')
                            <flux:sidebar.item icon="map" :href="route('alumni.timeline.index')" :current="request()->routeIs('alumni.timeline.*')" wire:navigate>
                                {{ __('Timeline Perjalanan') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endcan

                @if (auth()->user()->can('update-own-alumni-profile') || auth()->user()->can('view-whatsapp-analytics'))
                    <flux:sidebar.group :heading="__('Dokumentasi & Kenangan')" class="grid">
                        @can('update-own-alumni-profile')
                            <flux:sidebar.item icon="photo" :href="route('documentation.index')" :current="request()->routeIs('documentation.*')" wire:navigate>
                                {{ __('Arsip Dokumentasi') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="book-open" :href="route('memory-book.index')" :current="request()->routeIs('memory-book.*')" wire:navigate>
                                {{ __('Buku Kenangan') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('view-whatsapp-analytics')
                            <flux:sidebar.item icon="chart-bar" :href="route('whatsapp.analytics')" :current="request()->routeIs('whatsapp.analytics')" wire:navigate>
                                {{ __('WhatsApp Analytics') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif

                <flux:sidebar.group :heading="__('Komunitas')" class="grid">
                    <flux:sidebar.item icon="newspaper" :href="route('news.index')" :current="request()->routeIs('news.*')" wire:navigate>
                        {{ __('Kabar Alumni') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @can('update-own-alumni-profile')
                    <flux:sidebar.group :heading="__('Reuni 30 Tahun')" class="grid">
                        <flux:sidebar.item icon="calendar-days" :href="route('reunion.index')" :current="request()->routeIs('reunion.*')" wire:navigate>
                            {{ __('Overview') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="check-circle" :href="route('alumni.rsvp')" :current="request()->routeIs('alumni.rsvp')" wire:navigate>
                            {{ __('RSVP Saya') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="banknotes" :href="route('alumni.finance')" :current="request()->routeIs('alumni.finance')" wire:navigate>
                            {{ __('Pembayaran Saya') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="home" :href="route('alumni.room')" :current="request()->routeIs('alumni.room')" wire:navigate>
                            {{ __('Kamar Saya') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan

                @can('manage-alumni')
                    <flux:sidebar.group :heading="__('Administrasi Alumni')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('admin.alumni.index')" :current="request()->routeIs('admin.alumni.*')" wire:navigate>
                            {{ __('Data Alumni') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Administrasi Konten')" class="grid">
                        <flux:sidebar.item icon="photo" :href="route('admin.documentation.index')" :current="request()->routeIs('admin.documentation.*')" wire:navigate>
                            {{ __('Kelola Dokumentasi') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="tag" :href="route('admin.documentation-categories.index')" :current="request()->routeIs('admin.documentation-categories.*')" wire:navigate>
                            {{ __('Kategori Dokumentasi') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="newspaper" :href="route('admin.news.index')" :current="request()->routeIs('admin.news.*')" wire:navigate>
                            {{ __('Kelola Kabar') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Administrasi Reuni 30 Tahun')" class="grid">
                        <flux:sidebar.item icon="clipboard-document-check" :href="route('admin.rsvp.index')" :current="request()->routeIs('admin.rsvp.*')" wire:navigate>
                            {{ __('Monitoring RSVP') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="building-office-2" :href="route('admin.rooming.index')" :current="request()->routeIs('admin.rooming.*')" wire:navigate>
                            {{ __('Rooming') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="calendar-days" :href="route('admin.event-schedule.index')" :current="request()->routeIs('admin.event-schedule.*')" wire:navigate>
                            {{ __('Rangkaian Acara') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    @can('import-whatsapp-analytics')
                        <flux:sidebar.group :heading="__('Administrasi Analytics')" class="grid">
                            <flux:sidebar.item icon="chat-bubble-left-right" :href="route('admin.whatsapp.index')" :current="request()->routeIs('admin.whatsapp.*')" wire:navigate>
                                {{ __('WhatsApp Import') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endcan

                    @can('view-audit-logs')
                        <flux:sidebar.group :heading="__('Administrasi Sistem')" class="grid">
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.audit-logs.index')" :current="request()->routeIs('admin.audit-logs.*')" wire:navigate>
                                {{ __('Audit Log') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endcan
                @endcan

                @can('manage-finance')
                    <flux:sidebar.group :heading="__('Administrasi Keuangan')" class="grid">
                        <flux:sidebar.item icon="banknotes" :href="route('finance.index')" :current="request()->routeIs('finance.*')" wire:navigate>
                            {{ __('Pembayaran & Donasi') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endcan
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->whatsapp_number }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        @can('update-own-alumni-profile')
                            <flux:menu.item :href="route('alumni.profile')" icon="identification" wire:navigate>
                                {{ __('Profil Saya') }}
                            </flux:menu.item>
                        @endcan

                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Pengaturan Akun') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Keluar') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
