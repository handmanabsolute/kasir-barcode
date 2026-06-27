<div class="w-full px-6 py-6 max-w-5xl">
    <div class="flex items-center justify-between pb-5 border-b border-zinc-200 dark:border-zinc-700 mb-6">
        <div>
            <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $heading ?? 'Account Settings' }}</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $subheading ?? '' }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden shadow-xs">
        <div class="bg-zinc-50 dark:bg-zinc-800/50 px-6 py-3 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="font-semibold text-zinc-700 dark:text-zinc-300 text-sm">
                {{ $heading === 'Profile' ? 'Informasi Akun' : ($heading === 'Appearance' ? 'Pengaturan Tampilan' : 'Keamanan Akun') }}
            </h3>
        </div>
        <div class="p-6">
            {{ $slot }}
        </div>
    </div>
</div>

