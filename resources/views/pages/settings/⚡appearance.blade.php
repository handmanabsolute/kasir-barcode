<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    <div class="relative mb-6 w-full">
        <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Pengaturan</h1>
        <hr class="mt-6" style="border-color: var(--border-light); opacity: 0.6;" />
    </div>

    <div class="w-full">
        <div class="flex-1 self-stretch">
            <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Tampilan</h2>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Aplikasi menggunakan desain tetap yang sudah dioptimalkan.</p>

            <div class="mt-6 w-full max-w-lg">
                <div class="rounded-xl border border-[var(--border-light)] bg-[var(--card-bg)] p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl" style="background: #e0e7ff; color: #2563eb;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold" style="color: var(--text-primary);">Desain Tetap (Custom)</h3>
                            <p class="mt-1 text-sm leading-relaxed" style="color: var(--text-secondary);">
                                Tampilan aplikasi sudah diatur dengan desain khusus tanpa mode gelap/terang.
                                Sidebar menggunakan tema biru modern, dan halaman konten menggunakan latar putih bersih
                                yang dioptimalkan untuk kasir dan manajemen toko.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium" style="background: #ecfdf5; color: #065f46;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Tanpa mode gelap/terang
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium" style="background: #eff6ff; color: #1e40af;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                                    Sidebar biru modern
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium" style="background: #fefce8; color: #854d0e;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    Mudah disesuaikan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
