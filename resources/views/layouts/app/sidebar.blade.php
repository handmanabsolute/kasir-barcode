<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
        <script>
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            document.documentElement.style.colorScheme = 'light';
        </script>
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <div class="flex min-h-screen w-full">
            <!-- Leftmost Slim Sidebar (Desktop Only) -->
            <div class="hidden lg:flex w-[70px] bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex-col items-center py-5 justify-between shrink-0 h-screen sticky top-0">
                <!-- Top Logo -->
                <div class="flex flex-col items-center gap-6 w-full">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center h-10 w-10 rounded-xl bg-blue-600 text-white shadow-md shadow-blue-500/20">
                        <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </a>
                    
                    <div class="w-8 h-[1px] bg-slate-200 dark:bg-slate-800"></div>

                    <!-- Navigation Icons -->
                    <nav class="flex flex-col items-center gap-4 w-full px-2">
                        <flux:tooltip content="Dashboard" position="right">
                            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center h-10 w-10 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400 font-semibold shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <flux:icon.home class="h-5 w-5" />
                            </a>
                        </flux:tooltip>

                        <flux:tooltip content="Transaksi" position="right">
                            <a href="{{ route('transactions.index') }}" wire:navigate class="flex items-center justify-center h-10 w-10 rounded-xl transition-all duration-200 {{ request()->routeIs('transactions.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400 font-semibold shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <flux:icon.shopping-cart class="h-5 w-5" />
                            </a>
                        </flux:tooltip>

                        <flux:tooltip content="Produk" position="right">
                            <a href="{{ route('products.index') }}" wire:navigate class="flex items-center justify-center h-10 w-10 rounded-xl transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400 font-semibold shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <flux:icon.cube class="h-5 w-5" />
                            </a>
                        </flux:tooltip>

                        <flux:tooltip content="Kategori" position="right">
                            <a href="{{ route('categories.index') }}" wire:navigate class="flex items-center justify-center h-10 w-10 rounded-xl transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400 font-semibold shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <flux:icon.tag class="h-5 w-5" />
                            </a>
                        </flux:tooltip>
                    </nav>
                </div>

                <!-- Bottom Icons -->
                <div class="flex flex-col items-center gap-4 w-full px-2">
                    <!-- Scanner Active Indicator -->
                    <div class="relative">
                        <flux:tooltip content="Scanner Barcode Aktif" position="right">
                            <div class="flex items-center justify-center h-10 w-10 rounded-xl text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30">
                                <flux:icon.command-line class="h-5 w-5" />
                                <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            </div>
                        </flux:tooltip>
                    </div>

                    <flux:tooltip content="Pengaturan" position="right">
                        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center justify-center h-10 w-10 rounded-xl transition-all duration-200 {{ request()->routeIs('profile.edit') || request()->routeIs('appearance.edit') || request()->routeIs('security.edit') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400 font-semibold shadow-xs' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <flux:icon.cog class="h-5 w-5" />
                        </a>
                    </flux:tooltip>

                    <!-- Profile Dropdown -->
                    <flux:dropdown position="right" align="end">
                        <button class="h-10 w-10 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-sm font-semibold text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                            {{ auth()->user()->initials() }}
                        </button>
                        <flux:menu>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                Pengaturan
                            </flux:menu.item>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                                    Keluar
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>

            <!-- Secondary Sidebar (Desktop Only) -->
            <div class="hidden lg:flex w-60 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 flex-col py-6 shrink-0 h-screen sticky top-0">
                @php
                    $isSettings = request()->routeIs('profile.edit') || request()->routeIs('appearance.edit') || request()->routeIs('security.edit');
                @endphp
                
                <div class="px-6 mb-6">
                    <h2 class="text-base font-bold text-zinc-800 dark:text-zinc-200">
                        {{ $isSettings ? 'Pengaturan' : 'Kasir Barcode' }}
                    </h2>
                </div>

                <nav class="flex-1 px-4 space-y-1">
                    @if($isSettings)
                        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('profile.edit') ? 'bg-blue-50/70 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                            Akun / Profil
                        </a>
                        <a href="{{ route('appearance.edit') }}" wire:navigate class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('appearance.edit') ? 'bg-blue-50/70 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                            Tampilan
                        </a>
                        <a href="{{ route('security.edit') }}" wire:navigate class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('security.edit') ? 'bg-blue-50/70 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                            Keamanan
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50/70 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('transactions.index') }}" wire:navigate class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('transactions.*') ? 'bg-blue-50/70 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                            Transaksi
                        </a>
                        <a href="{{ route('products.index') }}" wire:navigate class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'bg-blue-50/70 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                            Produk
                        </a>
                        <a href="{{ route('categories.index') }}" wire:navigate class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('categories.*') ? 'bg-blue-50/70 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                            Kategori
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Content Area (Mobile/Desktop wrapped) -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Mobile only sidebar for backward compatibility/responsive toggling -->
                <flux:sidebar sticky collapsible="mobile" class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:sidebar.header>
                        <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                        <flux:sidebar.collapse class="lg:hidden" />
                    </flux:sidebar.header>

                    <flux:sidebar.nav>
                        <flux:sidebar.group :heading="__('Menu')" class="grid">
                            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                                Dashboard
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="shopping-cart" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" wire:navigate>
                                Transaksi
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="cube" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                                Produk
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="tag" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>
                                Kategori
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.edit') || request()->routeIs('appearance.edit') || request()->routeIs('security.edit')" wire:navigate>
                                Pengaturan
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    </flux:sidebar.nav>

                    <flux:spacer />

                    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
                </flux:sidebar>

                <!-- Mobile User Menu Header -->
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
                                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <flux:menu.radio.group>
                                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                    {{ __('Settings') }}
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
                                    {{ __('Log out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </flux:header>

                <!-- Main Slot -->
                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

        <!-- Global Barcode Scanner Listener -->
        <script>
            (function() {
                let barcodeBuffer = '';
                let lastKeyTime = Date.now();

                function playBeep() {
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioCtx.createOscillator();
                        const gainNode = audioCtx.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioCtx.destination);

                        oscillator.type = 'sine';
                        oscillator.frequency.setValueAtTime(1000, audioCtx.currentTime);
                        gainNode.gain.setValueAtTime(0.08, audioCtx.currentTime);

                        oscillator.start();
                        oscillator.stop(audioCtx.currentTime + 0.08);
                    } catch (e) {
                        console.warn("Audio beep failed:", e);
                    }
                }

                window.addEventListener('keydown', function(e) {
                    const currentTime = Date.now();
                    const timeDiff = currentTime - lastKeyTime;
                    lastKeyTime = currentTime;

                    // Barcode scanners enter characters extremely fast (usually < 30ms gap)
                    // If delay is > 50ms, it is highly likely human typing, so reset
                    if (timeDiff > 50) {
                        barcodeBuffer = '';
                    }

                    if (e.key === 'Enter') {
                        if (barcodeBuffer.length >= 4) {
                            e.preventDefault();
                            const scannedCode = barcodeBuffer;
                            barcodeBuffer = '';
                            
                            playBeep();

                            // Dispatch global event
                            window.dispatchEvent(new CustomEvent('barcode-scanned', { detail: scannedCode }));
                            
                            if (window.Livewire) {
                                window.Livewire.dispatch('toast', { 
                                    text: `Scan USB Berhasil: ${scannedCode}`, 
                                    variant: 'success' 
                                });
                            }
                        } else {
                            barcodeBuffer = '';
                        }
                        return;
                    }

                    // Keep only alphanumeric characters in buffer
                    if (e.key.length === 1 && /[a-zA-Z0-9]/.test(e.key)) {
                        barcodeBuffer += e.key;
                    }
                });
            })();
        </script>
    </body>
</html>
