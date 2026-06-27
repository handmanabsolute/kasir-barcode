<?php

use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Laporan')] class extends Component {
    public string $period = 'daily';

    public string $reportDate = '';

    public string $reportMonth = '';

    public string $reportYear = '';

    public ?array $reportData = null;

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
        $this->reportMonth = now()->format('m');
        $this->reportYear = now()->format('Y');
    }

    public function generateReport(): void
    {
        if ($this->period === 'daily') {
            $this->generateDailyReport();
        } else {
            $this->generateMonthlyReport();
        }
    }

    protected function generateDailyReport(): void
    {
        $date = $this->reportDate ?: now()->format('Y-m-d');

        $sales = Sale::query()
            ->whereDate('sold_at', $date)
            ->where('status', 'completed')
            ->get();

        $totalTransactions = $sales->count();
        $totalRevenue = (int) $sales->sum('total');
        $totalProfit = (int) $sales->sum('profit');
        $totalCost = (int) $sales->sum('total_cost');
        $averageTransaction = $totalTransactions > 0 ? (int) round($totalRevenue / $totalTransactions) : 0;

        $productSales = SaleItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_revenue, SUM(line_cost) as total_cost, SUM(line_profit) as total_profit')
            ->whereHas('sale', fn ($q) => $q->whereDate('sold_at', $date)->where('status', 'completed'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->get();

        $this->reportData = [
            'period' => 'daily',
            'label' => now()->parse($date)->isoFormat('dddd, D MMMM Y'),
            'date' => $date,
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'total_cost' => $totalCost,
            'average_transaction' => $averageTransaction,
            'products_sold' => (int) $productSales->sum('total_qty'),
            'product_sales' => $productSales->toArray(),
        ];
    }

    protected function generateMonthlyReport(): void
    {
        $month = str_pad($this->reportMonth ?: now()->format('m'), 2, '0', STR_PAD_LEFT);
        $year = $this->reportYear ?: now()->format('Y');
        $startDate = "{$year}-{$month}-01";
        $endDate = now()->parse($startDate)->endOfMonth()->format('Y-m-d');

        $sales = Sale::query()
            ->whereDate('sold_at', '>=', $startDate)
            ->whereDate('sold_at', '<=', $endDate)
            ->where('status', 'completed')
            ->get();

        $totalTransactions = $sales->count();
        $totalRevenue = (int) $sales->sum('total');
        $totalProfit = (int) $sales->sum('profit');
        $totalCost = (int) $sales->sum('total_cost');
        $averageTransaction = $totalTransactions > 0 ? (int) round($totalRevenue / $totalTransactions) : 0;

        $productSales = SaleItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_revenue, SUM(line_cost) as total_cost, SUM(line_profit) as total_profit')
            ->whereHas('sale', fn ($q) => $q
                ->whereDate('sold_at', '>=', $startDate)
                ->whereDate('sold_at', '<=', $endDate)
                ->where('status', 'completed'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->get();

        $dailyBreakdown = Sale::query()
            ->selectRaw('DATE(sold_at) as sale_date, COUNT(*) as transaction_count, SUM(total) as daily_revenue, SUM(profit) as daily_profit')
            ->whereDate('sold_at', '>=', $startDate)
            ->whereDate('sold_at', '<=', $endDate)
            ->where('status', 'completed')
            ->groupByRaw('DATE(sold_at)')
            ->orderBy('sale_date')
            ->get();

        $monthName = now()->parse($startDate)->isoFormat('MMMM Y');

        $this->reportData = [
            'period' => 'monthly',
            'label' => $monthName,
            'month' => $month,
            'year' => $year,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'total_cost' => $totalCost,
            'average_transaction' => $averageTransaction,
            'products_sold' => (int) $productSales->sum('total_qty'),
            'product_sales' => $productSales->toArray(),
            'daily_breakdown' => $dailyBreakdown->toArray(),
        ];
    }

    public function downloadPdf()
    {
        if (! $this->reportData) {
            $this->generateReport();
        }

        /** @var array $data */
        $data = $this->reportData;
        $totalRevenue = (int) $data['total_revenue'];
        $totalProfit = (int) $data['total_profit'];
        $totalCost = (int) $data['total_cost'];
        $averageTransaction = (int) $data['average_transaction'];

        $period = $data['period'];
        $datePart = $period === 'daily' ? $data['date'] : $data['year'] . '-' . $data['month'];
        $filename = 'laporan-penjualan-' . $datePart . '.pdf';

        $pdf = Pdf::loadView('pdf.report', [
            'period' => $period,
            'label' => $data['label'],
            'total_transactions' => $data['total_transactions'],
            'total_revenue_formatted' => Money::format($totalRevenue),
            'total_profit_formatted' => Money::format($totalProfit),
            'total_cost_formatted' => Money::format($totalCost),
            'average_transaction_formatted' => Money::format($averageTransaction),
            'profit_margin' => $totalRevenue > 0 ? number_format($totalProfit / $totalRevenue * 100, 1) : '0',
            'products_sold' => (int) $data['products_sold'],
            'product_sales' => $data['product_sales'],
            'daily_breakdown' => $data['daily_breakdown'] ?? [],
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
};
?>
<div class="flex w-full flex-col gap-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <flux:heading size="xl">Laporan Penjualan</flux:heading>
            <flux:text class="mt-1">Lihat dan download laporan harian dan bulanan penjualan toko dalam format PDF.</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button
                icon="arrow-down-tray"
                variant="primary"
                wire:click="downloadPdf"
                x-bind:disabled="!$wire.reportData"
            >
                Download PDF
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex flex-wrap items-end gap-4">
                {{-- Period Tabs --}}
                <div class="flex rounded-lg border border-neutral-200 p-0.5 dark:border-neutral-700">
                    <button
                        wire:click="$set('period', 'daily')"
                        class="rounded-md px-4 py-2 text-sm font-medium transition-all duration-150
                            {{ $period === 'daily' ? 'bg-blue-600 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}"
                    >
                        Harian
                    </button>
                    <button
                        wire:click="$set('period', 'monthly')"
                        class="rounded-md px-4 py-2 text-sm font-medium transition-all duration-150
                            {{ $period === 'monthly' ? 'bg-blue-600 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}"
                    >
                        Bulanan
                    </button>
                </div>

                {{-- Daily Date Picker --}}
                @if ($period === 'daily')
                    <div class="w-48">
                        <flux:input
                            wire:model="reportDate"
                            type="date"
                            label="Tanggal"
                        />
                    </div>
                @endif

                {{-- Monthly Picker --}}
                @if ($period === 'monthly')
                    <div class="w-32">
                        <flux:select wire:model="reportMonth" label="Bulan">
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $name)
                                <flux:select.option value="{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="w-28">
                        <flux:input
                            wire:model="reportYear"
                            type="number"
                            min="2020"
                            max="2099"
                            label="Tahun"
                        />
                    </div>
                @endif
            </div>

            <flux:button wire:click="generateReport" variant="primary" icon="arrow-path" class="shrink-0">
                Tampilkan Laporan
            </flux:button>
        </div>
    </div>

    {{-- Report Content --}}
    @if ($reportData)
        <div class="flex flex-col gap-6">
            {{-- Summary Cards --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Transaksi</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ number_format($reportData['total_transactions'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-zinc-400">
                        {{ $period === 'daily' ? 'Harian' : 'Bulan' }}: {{ $reportData['label'] }}
                    </p>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pendapatan</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ Money::format($reportData['total_revenue']) }}</p>
                    <p class="mt-1 text-xs text-zinc-400">
                        Rata-rata {{ Money::format($reportData['average_transaction']) }}/transaksi
                    </p>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Laba Kotor</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ Money::format($reportData['total_profit']) }}</p>
                    <p class="mt-1 text-xs text-zinc-400">
                        {{ $reportData['total_revenue'] > 0 ? number_format($reportData['total_profit'] / $reportData['total_revenue'] * 100, 1) : 0 }}% margin
                    </p>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Produk Terjual</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ number_format($reportData['products_sold'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-zinc-400">Total unit terjual</p>
                </div>
            </div>

            {{-- Product Breakdown Table --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">Rincian Produk Terjual</flux:heading>
                <flux:text class="mb-4 text-xs">Daftar produk yang terjual beserta pendapatan dan laba.</flux:text>

                @if (count($reportData['product_sales']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                    <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Produk</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Terjual</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pendapatan</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Modal</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Laba</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                @foreach ($reportData['product_sales'] as $item)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                        <td class="py-3 font-medium text-zinc-900 dark:text-white">{{ $item['product_name'] }}</td>
                                        <td class="py-3 text-right">{{ number_format((int) $item['total_qty'], 0, ',', '.') }}</td>
                                        <td class="py-3 text-right">{{ Money::format((int) $item['total_revenue']) }}</td>
                                        <td class="py-3 text-right text-zinc-500">{{ Money::format((int) $item['total_cost']) }}</td>
                                        <td class="py-3 text-right font-medium text-emerald-600 dark:text-emerald-400">{{ Money::format((int) $item['total_profit']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-neutral-300 dark:border-neutral-600 font-semibold">
                                    <td class="pt-3 text-zinc-900 dark:text-white">Total</td>
                                    <td class="pt-3 text-right">{{ number_format(collect($reportData['product_sales'])->sum('total_qty'), 0, ',', '.') }}</td>
                                    <td class="pt-3 text-right">{{ Money::format($reportData['total_revenue']) }}</td>
                                    <td class="pt-3 text-right text-zinc-500">{{ Money::format($reportData['total_cost']) }}</td>
                                    <td class="pt-3 text-right text-emerald-600 dark:text-emerald-400">{{ Money::format($reportData['total_profit']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <flux:icon.shopping-bag class="mb-2 h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                        <p class="text-sm font-medium text-zinc-400">Tidak ada data penjualan</p>
                        <p class="mt-1 text-xs text-zinc-400">Belum ada transaksi pada periode ini.</p>
                    </div>
                @endif
            </div>

            {{-- Daily Breakdown for Monthly Report --}}
            @if ($period === 'monthly' && !empty($reportData['daily_breakdown']))
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                    <flux:heading size="lg">Rincian Harian</flux:heading>
                    <flux:text class="mb-4 text-xs">Penjualan per hari selama {{ $reportData['label'] }}.</flux:text>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                    <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tanggal</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Transaksi</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pendapatan</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Laba</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                @foreach ($reportData['daily_breakdown'] as $day)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                        <td class="py-2.5 font-medium text-zinc-900 dark:text-white">{{ now()->parse($day['sale_date'])->isoFormat('dddd, D MMMM') }}</td>
                                        <td class="py-2.5 text-right">{{ number_format((int) $day['transaction_count'], 0, ',', '.') }}</td>
                                        <td class="py-2.5 text-right">{{ Money::format((int) $day['daily_revenue']) }}</td>
                                        <td class="py-2.5 text-right font-medium text-emerald-600 dark:text-emerald-400">{{ Money::format((int) $day['daily_profit']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white py-20 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:icon.chart-bar class="mb-4 h-16 w-16 text-zinc-200 dark:text-zinc-700" />
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">Belum Ada Laporan</h3>
            <p class="mt-1 max-w-sm text-center text-sm text-zinc-500">
                Pilih periode dan klik "Tampilkan Laporan" untuk melihat data penjualan.
            </p>
        </div>
    @endif
</div>
