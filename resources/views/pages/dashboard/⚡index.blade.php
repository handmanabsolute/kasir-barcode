<?php

use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Money;
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

        $recentSales = Sale::query()
            ->with('user')
            ->where('status', 'completed')
            ->latest('sold_at')
            ->limit(5)
            ->get();

        return compact('transactionCount', 'revenue', 'profit', 'productsSold', 'recentSales');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl">Dashboard Kasir</flux:heading>
        <flux:text class="mt-1">Ringkasan penjualan dan laba hari ini.</flux:text>
    </div>

    <div class="grid auto-rows-min gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500">Transaksi hari ini</flux:text>
            <flux:heading size="xl" class="mt-2">{{ number_format($transactionCount, 0, ',', '.') }}</flux:heading>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500">Pendapatan hari ini</flux:text>
            <flux:heading size="xl" class="mt-2">{{ Money::format($revenue) }}</flux:heading>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500">Laba hari ini</flux:text>
            <flux:heading size="xl" class="mt-2 text-emerald-600 dark:text-emerald-400">{{ Money::format($profit) }}</flux:heading>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:text class="text-sm text-zinc-500">Produk terjual</flux:text>
            <flux:heading size="xl" class="mt-2">{{ number_format($productsSold, 0, ',', '.') }}</flux:heading>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900">
            <div class="border-b border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="lg">Transaksi terbaru</flux:heading>
            </div>

            <div class="p-6">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Invoice</flux:table.column>
                        <flux:table.column>Kasir</flux:table.column>
                        <flux:table.column>Total</flux:table.column>
                        <flux:table.column>Laba</flux:table.column>
                        <flux:table.column>Waktu</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($recentSales as $sale)
                            <flux:table.row wire:key="sale-{{ $sale->id }}">
                                <flux:table.cell>{{ $sale->invoice_number }}</flux:table.cell>
                                <flux:table.cell>{{ $sale->user->name }}</flux:table.cell>
                                <flux:table.cell>{{ Money::format($sale->total) }}</flux:table.cell>
                                <flux:table.cell class="text-emerald-600 dark:text-emerald-400">{{ Money::format($sale->profit) }}</flux:table.cell>
                                <flux:table.cell>{{ $sale->sold_at->format('d/m/Y H:i') }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5">Belum ada transaksi. Mulai dari menu Transaksi.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-neutral-300 bg-white p-8 text-center dark:border-neutral-700 dark:bg-zinc-900">
            <img src="{{ asset('icon.svg') }}" alt="{{ config('app.name') }}" class="mb-4 size-20 object-contain" />
            <flux:heading size="lg">Kasir Barcode</flux:heading>
            <flux:text class="mt-2">Kelola produk, scan barcode, dan pantau laba penjualan harian.</flux:text>
            <div class="mt-4 flex flex-col gap-2">
                <flux:button :href="route('transactions.index')" wire:navigate variant="primary">Buka transaksi</flux:button>
                <flux:button :href="route('products.index')" wire:navigate variant="ghost">Kelola produk</flux:button>
            </div>
        </div>
    </div>
</div>
