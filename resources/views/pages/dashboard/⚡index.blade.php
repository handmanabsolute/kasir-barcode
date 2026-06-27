<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard Kasir')] class extends Component {

    public function with(): array
    {
        $todaySales = Sale::query()
            ->whereDate('sold_at', today())
            ->where('status', 'completed');

        $transactionCount = (clone $todaySales)->count();
        $revenue = (clone $todaySales)->sum('total');
        $profit = (clone $todaySales)->sum('profit');
        $productsSold = SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereDate('sold_at', today())->where('status', 'completed'))
            ->sum('quantity');

        // Top 5 best-selling products today
        $topProducts = SaleItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_revenue')
            ->whereHas('sale', fn ($query) => $query->whereDate('sold_at', today())->where('status', 'completed'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Hourly sales breakdown for today (database-agnostic: fetch all, group in PHP)
        $todaySalesData = Sale::query()
            ->select(['sold_at', 'total'])
            ->whereDate('sold_at', today())
            ->where('status', 'completed')
            ->get();

        $hourlyData = [];
        $maxHourlyRevenue = 0;
        for ($i = 0; $i < 24; $i++) {
            $hourKey = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $hourlyData[$i] = ['hour' => $hourKey, 'revenue' => 0, 'count' => 0];
        }

        foreach ($todaySalesData as $sale) {
            $hour = (int) $sale->sold_at->format('H');
            $hourlyData[$hour]['revenue'] += (int) $sale->total;
            $hourlyData[$hour]['count']++;
            if ($hourlyData[$hour]['revenue'] > $maxHourlyRevenue) {
                $maxHourlyRevenue = $hourlyData[$hour]['revenue'];
            }
        }

        $hourlyData = array_values($hourlyData);

        // Low stock products
        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(8)
            ->get();

        $productCount = Product::query()->where('is_active', true)->count();

        // Average transaction value today
        $avgTransactionValue = $transactionCount > 0
            ? (int) round($revenue / $transactionCount)
            : 0;

        $recentSales = Sale::query()
            ->with('user')
            ->where('status', 'completed')
            ->latest('sold_at')
            ->limit(5)
            ->get();

        $currentDate = now()->isoFormat('dddd, D MMMM Y');

        return compact(
            'transactionCount',
            'revenue',
            'profit',
            'productsSold',
            'topProducts',
            'hourlyData',
            'maxHourlyRevenue',
            'lowStockProducts',
            'productCount',
            'avgTransactionValue',
            'recentSales',
            'currentDate',
        );
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 pb-8">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Dashboard</flux:heading>
            <flux:text class="mt-1">{{ $currentDate }} — Ringkasan penjualan dan aktivitas toko hari ini.</flux:text>
        </div>
        <div class="flex items-center gap-3">
            <flux:button
                :href="route('transactions.index')"
                wire:navigate
                variant="primary"
                icon="shopping-cart"
            >
                Transaksi Baru
            </flux:button>
            <flux:button
                :href="route('products.index')"
                wire:navigate
                variant="ghost"
                icon="cube"
            >
                Kelola Produk
            </flux:button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Card: Transaksi --}}
        <div class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-blue-50 transition-all duration-300 group-hover:scale-150 group-hover:bg-blue-100 dark:bg-blue-950/30 dark:group-hover:bg-blue-950/50"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Transaksi Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ number_format($transactionCount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-zinc-400">Total transaksi selesai</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <flux:icon.shopping-cart class="h-6 w-6" />
                </div>
            </div>
        </div>

        {{-- Card: Pendapatan --}}
        <div class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-emerald-50 transition-all duration-300 group-hover:scale-150 group-hover:bg-emerald-100 dark:bg-emerald-950/30 dark:group-hover:bg-emerald-950/50"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pendapatan Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ Money::format($revenue) }}</p>
                    <p class="mt-1 text-xs text-zinc-400">
                        Rata-rata {{ Money::format($avgTransactionValue) }}/transaksi
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <flux:icon.banknotes class="h-6 w-6" />
                </div>
            </div>
        </div>

        {{-- Card: Laba --}}
        <div class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-violet-50 transition-all duration-300 group-hover:scale-150 group-hover:bg-violet-100 dark:bg-violet-950/30 dark:group-hover:bg-violet-950/50"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Laba Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ Money::format($profit) }}</p>
                    <p class="mt-1 text-xs text-zinc-400">
                        {{ $revenue > 0 ? number_format($profit / max($revenue, 1) * 100, 1) : 0 }}% margin keuntungan
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400">
                    <flux:icon.arrow-trending-up class="h-6 w-6" />
                </div>
            </div>
        </div>

        {{-- Card: Produk Terjual --}}
        <div class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-amber-50 transition-all duration-300 group-hover:scale-150 group-hover:bg-amber-100 dark:bg-amber-950/30 dark:group-hover:bg-amber-950/50"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Produk Terjual</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ number_format($productsSold, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-zinc-400">{{ $productCount }} produk aktif tersedia</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <flux:icon.shopping-bag class="h-6 w-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        {{-- Left Column --}}
        <div class="flex flex-col gap-6">
            {{-- Sales Chart --}}
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg">Aktivitas Penjualan Hari Ini</flux:heading>
                            <flux:text class="text-xs">Grafik pendapatan per jam</flux:text>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-zinc-500">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block h-2.5 w-2.5 rounded-sm bg-blue-500"></span>
                                Pendapatan
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block h-2.5 w-2.5 rounded-sm bg-blue-200"></span>
                                Transaksi
                            </span>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-5 pt-4">
                    @if ($maxHourlyRevenue > 0)
                        @php
                            $chartMax = $maxHourlyRevenue;
                        @endphp
                        <div class="flex items-end gap-[3px] sm:gap-1 h-40">
                            @foreach ($hourlyData as $data)
                                <div class="group/chart relative flex flex-1 flex-col items-center justify-end h-full">
                                    {{-- Revenue bar --}}
                                    <div
                                        class="relative w-full max-w-[20px] rounded-t-md bg-gradient-to-t from-blue-600 to-blue-400 transition-all duration-300 hover:from-blue-700 hover:to-blue-500"
                                        style="height: {{ max(2, ($data['revenue'] / $chartMax) * 100) }}%;"
                                    >
                                        {{-- Tooltip --}}
                                        <div class="absolute -top-2 left-1/2 z-10 hidden -translate-x-1/2 -translate-y-full group-hover/chart:block">
                                            <div class="whitespace-nowrap rounded-lg bg-zinc-800 px-2.5 py-1.5 text-xs text-white shadow-lg dark:bg-zinc-700">
                                                <div class="font-medium">{{ Money::format($data['revenue']) }}</div>
                                                <div class="text-zinc-300">{{ $data['count'] }} transaksi</div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Count indicator dot --}}
                                    @if ($data['count'] > 0)
                                        <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-blue-200 dark:bg-blue-300"></div>
                                    @endif
                                    {{-- Hour label --}}
                                    <span class="mt-1 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 {{ $loop->index % 2 === 0 ? '' : 'hidden sm:inline' }}">
                                        {{ (int) $data['hour'] }}:00
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <flux:icon.chart-bar class="mb-2 h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                            <p class="text-sm font-medium text-zinc-400">Belum ada data penjualan hari ini</p>
                            <p class="mt-1 text-xs text-zinc-400">Mulai transaksi untuk melihat grafik aktivitas penjualan</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg">Transaksi Terbaru</flux:heading>
                            <flux:text class="text-xs">5 transaksi terakhir</flux:text>
                        </div>
                        <flux:button :href="route('transactions.index')" wire:navigate size="sm" variant="ghost" icon-trailing="arrow-right">
                            Lihat semua
                        </flux:button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice</th>
                                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Kasir</th>
                                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-right">Total</th>
                                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-right">Laba</th>
                                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @forelse ($recentSales as $sale)
                                <tr class="transition-colors hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-3.5">
                                        <span class="font-medium text-blue-600 dark:text-blue-400">{{ $sale->invoice_number }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 text-zinc-600 dark:text-zinc-400">{{ $sale->user->name }}</td>
                                    <td class="px-6 py-3.5 text-right font-medium text-zinc-900 dark:text-white">{{ Money::format($sale->total) }}</td>
                                    <td class="px-6 py-3.5 text-right font-medium text-emerald-600 dark:text-emerald-400">{{ Money::format($sale->profit) }}</td>
                                    <td class="px-6 py-3.5 text-right text-zinc-500 dark:text-zinc-400">{{ $sale->sold_at->format('H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-zinc-400">
                                        <flux:icon.document-text class="mx-auto mb-2 h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                                        Belum ada transaksi. Mulai dari menu Transaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="flex flex-col gap-6">
            {{-- Top Products --}}
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">Produk Terlaris</flux:heading>
                    <flux:text class="text-xs">5 produk paling laku hari ini</flux:text>
                </div>
                <div class="p-5">
                    @forelse ($topProducts as $product)
                        <div class="group flex items-center gap-3 py-2.5 {{ !$loop->last ? 'border-b border-neutral-100 dark:border-neutral-800' : '' }}">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 text-xs font-bold text-blue-600 dark:from-blue-950/50 dark:to-indigo-950/50 dark:text-blue-400">
                                {{ $loop->iteration }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $product->product_name }}</p>
                                <p class="text-xs text-zinc-400">{{ number_format((int) $product->total_qty) }} terjual</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ Money::format((int) $product->total_revenue) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <flux:icon.cube class="mb-2 h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                            <p class="text-sm text-zinc-400">Belum ada produk terjual hari ini</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Low Stock Alerts --}}
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                            <flux:icon.exclamation-triangle class="h-3.5 w-3.5" />
                        </div>
                        <div>
                            <flux:heading size="lg">Stok Menipis</flux:heading>
                            <flux:text class="text-xs">Produk dengan stok tersisa sedikit</flux:text>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    @forelse ($lowStockProducts as $product)
                        <div class="flex items-center gap-3 py-2.5 {{ !$loop->last ? 'border-b border-neutral-100 dark:border-neutral-800' : '' }}">
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $product->name }}</p>
                                <p class="text-xs text-zinc-400">{{ $product->category?->name ?? 'Tanpa kategori' }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $product->stock <= 2 ? 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400' }}">
                                    {{ $product->stock }} tersisa
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <flux:icon.check-circle class="mb-2 h-8 w-8 text-emerald-400" />
                            <p class="text-sm text-zinc-400">Semua stok produk aman</p>
                        </div>
                    @endforelse
                    @if ($lowStockProducts->isNotEmpty())
                        <div class="mt-3 pt-2 text-center">
                            <flux:button :href="route('products.index')" wire:navigate size="sm" variant="ghost" icon-trailing="arrow-right">
                                Kelola stok produk
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
