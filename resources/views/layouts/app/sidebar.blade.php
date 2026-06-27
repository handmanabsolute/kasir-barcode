<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
        <script>
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            document.documentElement.style.colorScheme = 'light';
        </script>
        <style>
            /* ============================================
               SIDEBAR MODERN — Fixed light theme
               ============================================ */
            .sidebar-modern {
                --sidebar-bg: #f8f9fc;
                --sidebar-border: #e2e5ed;
                --sidebar-text: #4a5068;
                --sidebar-text-muted: #8b92a8;
                --sidebar-hover-bg: #eef0f6;
                --sidebar-hover-text: #1a1d2e;
                --sidebar-active-bg: #e8edff;
                --sidebar-active-text: #3b5de7;
                --sidebar-active-border: #3b5de7;
                --sidebar-brand-bg: #ffffff;
                --sidebar-brand-shadow: 0 1px 3px rgba(0,0,0,0.06);
                --sidebar-divider: #e2e5ed;
                --sidebar-header-bg: #ffffff;
                --sidebar-profile-bg: #ffffff;
                --sidebar-profile-border: #e2e5ed;
                --sidebar-profile-shadow: 0 1px 2px rgba(0,0,0,0.04);
                --sidebar-badge-bg: #3b5de7;
                --sidebar-badge-text: #ffffff;
                --sidebar-logout-text: #dc2626;
                --sidebar-logout-hover: #fef2f2;
                --sidebar-logout-border: #fecaca;
            }

            .sidebar-modern {
                background: var(--sidebar-bg);
                border-right: 1px solid var(--sidebar-border);
            }

            /* ── Header / Brand ── */
            .sidebar-modern .sidebar-header-area {
                background: var(--sidebar-header-bg);
                border-bottom: 1px solid var(--sidebar-divider);
                padding: 18px 20px 14px;
                margin: 0 0 8px;
            }

            .sidebar-modern .sidebar-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
            }

            .sidebar-modern .sidebar-brand-icon {
                width: 38px;
                height: 38px;
                background: linear-gradient(135deg, #3b5de7, #6d8cff);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 8px rgba(59, 93, 231, 0.3);
                flex-shrink: 0;
            }

            .sidebar-modern .sidebar-brand-icon img {
                width: 20px;
                height: 20px;
                filter: brightness(0) invert(1);
            }

            .sidebar-modern .sidebar-brand-text {
                display: flex;
                flex-direction: column;
                line-height: 1.2;
            }

            .sidebar-modern .sidebar-brand-name {
                font-size: 15px;
                font-weight: 700;
                color: var(--sidebar-hover-text);
                letter-spacing: -0.3px;
            }

            .sidebar-modern .sidebar-brand-sub {
                font-size: 10px;
                font-weight: 500;
                color: var(--sidebar-text-muted);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            /* ── Section label ── */
            .sidebar-modern .sidebar-section-label {
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.8px;
                color: var(--sidebar-text-muted);
                padding: 16px 20px 6px;
            }

            /* ── Nav items ── */
            .sidebar-modern .sidebar-nav-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 20px;
                margin: 2px 10px;
                border-radius: 8px;
                color: var(--sidebar-text);
                text-decoration: none;
                font-size: 13.5px;
                font-weight: 500;
                transition: all 0.15s ease;
                position: relative;
                border: none;
                background: transparent;
                width: auto;
                cursor: pointer;
            }

            .sidebar-modern .sidebar-nav-item:hover {
                background: var(--sidebar-hover-bg);
                color: var(--sidebar-hover-text);
            }

            .sidebar-modern .sidebar-nav-item:active {
                transform: scale(0.98);
            }

            .sidebar-modern .sidebar-nav-item.current,
            .sidebar-modern .sidebar-nav-item.active {
                background: var(--sidebar-active-bg);
                color: var(--sidebar-active-text);
                font-weight: 600;
            }

            .sidebar-modern .sidebar-nav-item.current::before,
            .sidebar-modern .sidebar-nav-item.active::before {
                content: '';
                position: absolute;
                left: -10px;
                top: 50%;
                transform: translateY(-50%);
                width: 3px;
                height: 20px;
                background: var(--sidebar-active-border);
                border-radius: 0 3px 3px 0;
            }

            .sidebar-modern .sidebar-nav-icon {
                width: 20px;
                height: 20px;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar-modern .sidebar-nav-icon svg {
                width: 18px;
                height: 18px;
            }

            /* ── Profile ── */
            .sidebar-modern .sidebar-profile-area {
                margin: 8px 10px 4px;
                padding: 10px 12px;
                background: var(--sidebar-profile-bg);
                border: 1px solid var(--sidebar-profile-border);
                border-radius: 10px;
                box-shadow: var(--sidebar-profile-shadow);
                transition: all 0.15s ease;
            }

            .sidebar-modern .sidebar-profile-area:hover {
                border-color: var(--sidebar-active-border);
                box-shadow: 0 2px 8px rgba(59, 93, 231, 0.1);
            }

            .sidebar-modern .sidebar-profile-inner {
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
            }

            .sidebar-modern .sidebar-profile-avatar {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                background: linear-gradient(135deg, #3b5de7, #6d8cff);
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: 700;
                flex-shrink: 0;
            }

            .sidebar-modern .sidebar-profile-info {
                flex: 1;
                min-width: 0;
                line-height: 1.2;
            }

            .sidebar-modern .sidebar-profile-name {
                font-size: 13px;
                font-weight: 600;
                color: var(--sidebar-hover-text);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .sidebar-modern .sidebar-profile-email {
                font-size: 10.5px;
                color: var(--sidebar-text-muted);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .sidebar-modern .sidebar-profile-chevron {
                color: var(--sidebar-text-muted);
                flex-shrink: 0;
                transition: transform 0.2s ease;
            }

            .sidebar-modern .sidebar-profile-area:hover .sidebar-profile-chevron {
                transform: translateX(3px);
            }

            /* ── Logout Button ── */
            .sidebar-modern .sidebar-logout-btn {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 4px 10px 12px;
                padding: 10px 14px;
                border-radius: 8px;
                color: var(--sidebar-logout-text);
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                border: 1px solid transparent;
                background: transparent;
                transition: all 0.15s ease;
                cursor: pointer;
                width: auto;
            }

            .sidebar-modern .sidebar-logout-btn:hover {
                background: var(--sidebar-logout-hover);
                border-color: var(--sidebar-logout-border);
            }

            .sidebar-modern .sidebar-logout-btn:active {
                transform: scale(0.98);
            }

            .sidebar-modern .sidebar-logout-btn svg {
                width: 18px;
                height: 18px;
                flex-shrink: 0;
            }

            /* ── Divider ── */
            .sidebar-modern .sidebar-divider {
                height: 1px;
                background: var(--sidebar-divider);
                margin: 6px 20px;
            }

            /* ── Scrollbar ── */
            .sidebar-modern ::-webkit-scrollbar {
                width: 4px;
            }
            .sidebar-modern ::-webkit-scrollbar-track {
                background: transparent;
            }
            .sidebar-modern ::-webkit-scrollbar-thumb {
                background: var(--sidebar-divider);
                border-radius: 2px;
            }



            /* ── Mobile ── */
            @media (max-width: 1023px) {
                .sidebar-modern .sidebar-section-label {
                    padding: 12px 16px 4px;
                }
                .sidebar-modern .sidebar-nav-item {
                    padding: 8px 16px;
                    margin: 1px 8px;
                }
                .sidebar-modern .sidebar-profile-area {
                    margin: 4px 8px 4px;
                }
                .sidebar-modern .sidebar-logout-btn {
                    margin: 4px 8px 10px;
                }
            }

            [data-flux-sidebar] {
                --sidebar-width: 260px !important;
            }
            .sidebar-modern [data-flux-sidebar-header],
            .sidebar-modern [data-flux-sidebar-nav],
            .sidebar-modern [data-flux-sidebar-footer] {
                display: none !important;
            }
        </style>
    </head>
    <body class="min-h-screen bg-white">
        {{-- Sidebar --}}
        <div class="sidebar-modern fixed inset-y-0 left-0 z-30 flex w-[260px] flex-col overflow-hidden" data-flux-sidebar>
            {{-- Brand --}}
            <div class="sidebar-header-area">
                <a href="{{ route('dashboard') }}" class="sidebar-brand" wire:navigate>
                    <div class="sidebar-brand-icon">
                        <x-app-logo-icon class="size-5" />
                    </div>
                    <div class="sidebar-brand-text">
                        <span class="sidebar-brand-name">Kasir Barcode</span>
                        <span class="sidebar-brand-sub">Point of Sale</span>
                    </div>
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-2">
                <div class="sidebar-section-label">Menu</div>

                <a href="{{ route('dashboard') }}"
                    class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'current' : '' }}"
                    wire:navigate>
                    <span class="sidebar-nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('transactions.index') }}"
                    class="sidebar-nav-item {{ request()->routeIs('transactions.*') ? 'current' : '' }}"
                    wire:navigate>
                    <span class="sidebar-nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    </span>
                    <span>Transaksi</span>
                </a>

                <a href="{{ route('products.index') }}"
                    class="sidebar-nav-item {{ request()->routeIs('products.*') ? 'current' : '' }}"
                    wire:navigate>
                    <span class="sidebar-nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </span>
                    <span>Produk</span>
                </a>

                <a href="{{ route('categories.index') }}"
                    class="sidebar-nav-item {{ request()->routeIs('categories.*') ? 'current' : '' }}"
                    wire:navigate>
                    <span class="sidebar-nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
                    </span>
                    <span>Kategori</span>
                </a>

                <a href="{{ route('reports.index') }}"
                    class="sidebar-nav-item {{ request()->routeIs('reports.*') ? 'current' : '' }}"
                    wire:navigate>
                    <span class="sidebar-nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </span>
                    <span>Laporan</span>
                </a>
            </nav>

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}" class="sidebar-profile-area block no-underline" wire:navigate>
                <div class="sidebar-profile-inner">
                    <div class="sidebar-profile-avatar">{{ auth()->user()->initials() }}</div>
                    <div class="sidebar-profile-info">
                        <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-profile-email">{{ auth()->user()->email }}</div>
                    </div>
                    <svg class="sidebar-profile-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18 15 12 9 6"/></svg>
                </div>
            </a>

            {{-- Logout — standalone button --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>

        {{-- Main Content --}}
        <div class="min-h-screen lg:pl-[260px]">
            {{-- Mobile Header --}}
            <flux:header class="lg:hidden sticky top-0 z-20 border-b border-zinc-200 bg-white">
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

            <main>
                {{ $slot }}
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

        {{-- Mobile Overlay --}}
        <div id="sidebar-overlay"
            class="fixed inset-0 z-20 hidden bg-black/50 lg:hidden"
            onclick="toggleMobileSidebar()"
        ></div>

        {{-- Mobile Sidebar --}}
        <div id="mobile-sidebar"
            class="sidebar-modern fixed inset-y-0 left-0 z-30 w-[260px] -translate-x-full transform transition-transform duration-200 ease-in-out lg:hidden"
            data-flux-sidebar>
            <div class="sidebar-header-area">
                <div class="sidebar-brand">
                    <div class="sidebar-brand-icon">
                        <x-app-logo-icon class="size-5" />
                    </div>
                    <div class="sidebar-brand-text">
                        <span class="sidebar-brand-name">Kasir Barcode</span>
                        <span class="sidebar-brand-sub">Point of Sale</span>
                    </div>
                </div>
                <button onclick="toggleMobileSidebar()" class="absolute right-3 top-4 text-zinc-500 hover:text-zinc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto py-2">
                <div class="sidebar-section-label">Menu</div>
                <a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'current' : '' }}" onclick="toggleMobileSidebar()" wire:navigate>
                    <span class="sidebar-nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('transactions.index') }}" class="sidebar-nav-item {{ request()->routeIs('transactions.*') ? 'current' : '' }}" onclick="toggleMobileSidebar()" wire:navigate>
                    <span class="sidebar-nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg></span>
                    <span>Transaksi</span>
                </a>
                <a href="{{ route('products.index') }}" class="sidebar-nav-item {{ request()->routeIs('products.*') ? 'current' : '' }}" onclick="toggleMobileSidebar()" wire:navigate>
                    <span class="sidebar-nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
                    <span>Produk</span>
                </a>
                <a href="{{ route('categories.index') }}" class="sidebar-nav-item {{ request()->routeIs('categories.*') ? 'current' : '' }}" onclick="toggleMobileSidebar()" wire:navigate>
                    <span class="sidebar-nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg></span>
                    <span>Kategori</span>
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-nav-item {{ request()->routeIs('reports.*') ? 'current' : '' }}" onclick="toggleMobileSidebar()" wire:navigate>
                    <span class="sidebar-nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
                    <span>Laporan</span>
                </a>
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="px-2 pb-3">
                @csrf
                <button type="submit" class="sidebar-logout-btn w-full" onclick="toggleMobileSidebar()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>

        <script>
            function toggleMobileSidebar() {
                const sidebar = document.getElementById('mobile-sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (!sidebar || !overlay) return;
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                sidebar.classList.toggle('-translate-x-full', isOpen);
                sidebar.classList.toggle('translate-x-0', !isOpen);
                overlay.classList.toggle('hidden', isOpen);
                document.body.classList.toggle('overflow-hidden', !isOpen);
            }
            document.addEventListener('click', function(e) {
                const toggle = e.target.closest('[data-flux-sidebar-toggle]') || e.target.closest('.lg\\:hidden [class*="bars-2"]');
                if (toggle && window.innerWidth < 1024) toggleMobileSidebar();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const sidebar = document.getElementById('mobile-sidebar');
                    if (sidebar && !sidebar.classList.contains('-translate-x-full')) toggleMobileSidebar();
                }
            });
        </script>

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
                    } catch (e) { console.warn("Audio beep failed:", e); }
                }
                window.addEventListener('keydown', function(e) {
                    const currentTime = Date.now();
                    const timeDiff = currentTime - lastKeyTime;
                    lastKeyTime = currentTime;
                    if (timeDiff > 50) { barcodeBuffer = ''; }
                    if (e.key === 'Enter') {
                        if (barcodeBuffer.length >= 4) {
                            e.preventDefault();
                            const scannedCode = barcodeBuffer;
                            barcodeBuffer = '';
                            playBeep();
                            window.dispatchEvent(new CustomEvent('barcode-scanned', { detail: scannedCode }));
                        } else { barcodeBuffer = ''; }
                        return;
                    }
                    if (e.key.length === 1 && /[a-zA-Z0-9]/.test(e.key)) { barcodeBuffer += e.key; }
                });
            })();
        </script>
    </body>
</html>
