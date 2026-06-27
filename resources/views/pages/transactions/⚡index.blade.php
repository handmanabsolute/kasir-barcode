<?php

use App\Models\Product;
use App\Services\CheckoutService;
use App\Support\Money;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Transaksi Kasir')] class extends Component {
    public string $scanCode = '';

    /** @var array<int, array{product_id: int, name: string, barcode: string, quantity: int, unit_price: int, line_total: int}> */
    public array $cart = [];

    public int $paid_amount = 0;

    public ?array $completedSale = null;

    public function addByBarcode(): void
    {
        $barcode = trim($this->scanCode);

        if ($barcode === '') {
            return;
        }

        $product = Product::query()
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            Flux::toast(variant: 'danger', text: 'Produk dengan barcode '.$barcode.' tidak ditemukan.');
            $this->scanCode = '';

            return;
        }

        if ($product->stock < 1) {
            Flux::toast(variant: 'danger', text: 'Stok '.$product->name.' habis.');
            $this->scanCode = '';

            return;
        }

        $existingIndex = collect($this->cart)->search(fn (array $item): bool => $item['product_id'] === $product->id);

        if ($existingIndex !== false) {
            $currentQty = $this->cart[$existingIndex]['quantity'];

            if ($currentQty >= $product->stock) {
                Flux::toast(variant: 'danger', text: 'Stok '.$product->name.' tidak mencukupi.');
                $this->scanCode = '';

                return;
            }

            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['line_total'] = $this->cart[$existingIndex]['quantity'] * $product->sell_price;
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'quantity' => 1,
                'unit_price' => $product->sell_price,
                'line_total' => $product->sell_price,
            ];
        }

        $this->scanCode = '';
        Flux::toast(variant: 'success', text: $product->name.' ditambahkan ke keranjang.');
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $quantity = max(1, $quantity);
        $product = Product::query()->find($this->cart[$index]['product_id']);

        if ($product && $quantity > $product->stock) {
            Flux::toast(variant: 'danger', text: 'Stok maksimal '.$product->stock.'.');
            $quantity = $product->stock;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['line_total'] = $quantity * $this->cart[$index]['unit_price'];
    }

    public function removeItem(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->paid_amount = 0;
    }

    public function checkout(CheckoutService $checkoutService): void
    {
        $this->validate([
            'paid_amount' => ['required', 'integer', 'min:1'],
        ]);

        $items = collect($this->cart)->map(fn (array $item): array => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ])->all();

        $sale = $checkoutService->checkout(Auth::user(), $items, $this->paid_amount);

        $this->completedSale = $sale->toArray();
        $this->completedSale['items'] = $sale->items->toArray();
        $this->completedSale['sold_at_formatted'] = $sale->sold_at->format('d/m/Y H:i:s');
        $this->completedSale['subtotal_formatted'] = Money::format($sale->subtotal);
        $this->completedSale['total_formatted'] = Money::format($sale->total);
        $this->completedSale['paid_amount_formatted'] = Money::format($sale->paid_amount);
        $this->completedSale['change_amount_formatted'] = Money::format($sale->change_amount);

        $this->clearCart();

        Flux::toast(
            variant: 'success',
            text: 'Transaksi '.$sale->invoice_number.' berhasil. Laba: '.Money::format($sale->profit),
        );
    }

    public function cartTotal(): int
    {
        return collect($this->cart)->sum('line_total');
    }

    public function cartProfitEstimate(): int
    {
        return collect($this->cart)->sum(function (array $item): int {
            $product = Product::query()->find($item['product_id']);

            if (! $product) {
                return 0;
            }

            return ($product->sell_price - $product->cost_price) * $item['quantity'];
        });
    }
}; ?>

<div class="flex w-full flex-col gap-6" x-data="{
    init() {
        window.addEventListener('barcode-scanned', (e) => {
            this.$wire.set('scanCode', e.detail);
            this.$wire.addByBarcode();
        });
    }
}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">Transaksi Kasir</flux:heading>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Scanner USB: Aktif
                </span>
            </div>
            <flux:text class="mt-1">Scan barcode produk untuk menambah ke keranjang, lalu proses pembayaran.</flux:text>
        </div>
        <flux:modal.trigger name="usb-printer-modal">
            <flux:button icon="printer" onclick="window.showUsbPrinterSettings()" tooltip="Pengaturan Printer USB">
                Printer
            </flux:button>
        </flux:modal.trigger>
    </div>


    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="flex flex-col gap-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <form wire:submit="addByBarcode" class="flex flex-col gap-3 sm:flex-row">
                    <div class="flex-1">
                        <flux:input
                            wire:model="scanCode"
                            label="Scan barcode"
                            placeholder="Scan produk dengan alat barcode USB"
                            autofocus
                        />
                    </div>
                    <div class="sm:self-end">
                        <flux:button type="submit" variant="primary">Tambah</flux:button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Produk</flux:table.column>
                        <flux:table.column>Barcode</flux:table.column>
                        <flux:table.column>Qty</flux:table.column>
                        <flux:table.column>Harga</flux:table.column>
                        <flux:table.column>Subtotal</flux:table.column>
                        <flux:table.column class="w-24">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($cart as $index => $item)
                            <flux:table.row wire:key="cart-{{ $item['product_id'] }}-{{ $index }}">
                                <flux:table.cell>{{ $item['name'] }}</flux:table.cell>
                                <flux:table.cell>{{ $item['barcode'] }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        <flux:button size="sm" variant="ghost" wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})">-</flux:button>
                                        <span>{{ $item['quantity'] }}</span>
                                        <flux:button size="sm" variant="ghost" wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})">+</flux:button>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>{{ Money::format($item['unit_price']) }}</flux:table.cell>
                                <flux:table.cell>{{ Money::format($item['line_total']) }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button size="sm" variant="danger" wire:click="removeItem({{ $index }})">Hapus</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6">Keranjang masih kosong. Scan barcode produk untuk memulai.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="lg">Ringkasan</flux:heading>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <flux:text>Total belanja</flux:text>
                    <flux:heading>{{ Money::format($this->cartTotal()) }}</flux:heading>
                </div>
                <div class="flex items-center justify-between">
                    <flux:text>Estimasi laba</flux:text>
                    <flux:heading class="text-emerald-600 dark:text-emerald-400">{{ Money::format($this->cartProfitEstimate()) }}</flux:heading>
                </div>
            </div>

            <form 
                x-data 
                x-on:submit.prevent="
                    if (!localStorage.getItem('usb_receipt_printer_vendor_id')) {
                        if (window.Livewire) {
                            window.Livewire.dispatch('toast', { text: 'Silakan pilih printer struk terlebih dahulu.', variant: 'danger' });
                        }
                        $flux.modal('usb-printer-modal').show();
                    } else {
                        $wire.checkout();
                    }
                " 
                class="mt-6 flex flex-col gap-4"
            >
                <flux:input wire:model="paid_amount" type="number" min="0" label="Nominal bayar (Rp)" required />

                @if ($paid_amount >= $this->cartTotal() && $this->cartTotal() > 0)
                    <flux:text>Kembalian: {{ Money::format($paid_amount - $this->cartTotal()) }}</flux:text>
                @endif

                <flux:button type="submit" variant="primary" class="w-full" :disabled="empty($cart)">
                    Cetak Nota
                </flux:button>

                @if (count($cart) > 0)
                    <flux:button type="button" variant="ghost" wire:click="clearCart" class="w-full">Kosongkan keranjang</flux:button>
                @endif
            </form>



<flux:modal name="receipt-modal" :show="$completedSale !== null" class="max-w-md" @close="$wire.set('completedSale', null)">
    @if ($completedSale)
        <div class="space-y-6">
            <div class="text-center">
                <flux:heading size="lg">Transaksi Berhasil</flux:heading>
                <flux:text class="mt-1">Nomor Invoice: {{ $completedSale['invoice_number'] }}</flux:text>
            </div>

            <!-- Receipt Preview Container -->
            <div id="receipt-print-area" class="border border-neutral-200 p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800 text-black dark:text-white font-mono text-sm leading-relaxed max-h-[300px] overflow-y-auto">
                <!-- Receipt Content -->
                <div class="text-center font-bold text-base mb-2">KASIR BARCODE</div>
                <div class="text-center text-xs mb-3 border-b border-dashed border-neutral-300 pb-2">
                    {{ $completedSale['sold_at_formatted'] }}<br>
                    Invoice: {{ $completedSale['invoice_number'] }}<br>
                    Kasir: {{ auth()->user()->name }}
                </div>

                <table class="w-full text-xs mb-3 border-b border-dashed border-neutral-300 pb-2">
                    @foreach ($completedSale['items'] as $item)
                        <tr>
                            <td colspan="2" class="font-bold">{{ $item['product_name'] }}</td>
                        </tr>
                        <tr class="text-neutral-600 dark:text-neutral-400">
                            <td class="pb-2">{{ $item['quantity'] }} x {{ Money::format($item['unit_price']) }}</td>
                            <td class="text-right pb-2">{{ Money::format($item['line_total']) }}</td>
                        </tr>
                    @endforeach
                </table>

                <div class="text-xs space-y-1">
                    <div class="flex justify-between">
                        <span>Total:</span>
                        <span class="font-bold">{{ $completedSale['total_formatted'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bayar:</span>
                        <span>{{ $completedSale['paid_amount_formatted'] }}</span>
                    </div>
                    <div class="flex justify-between border-t border-dashed border-neutral-300 pt-1 font-bold">
                        <span>Kembali:</span>
                        <span>{{ $completedSale['change_amount_formatted'] }}</span>
                    </div>
                </div>

                <div class="text-center text-xs mt-4 pt-2 border-t border-dashed border-neutral-300">
                    Terima Kasih<br>Selamat Belanja Kembali!
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button variant="ghost" class="flex-1" wire:click="$set('completedSale', null)">
                    Tutup
                </flux:button>
                <flux:button variant="primary" class="flex-1" onclick="printReceipt()">
                    Cetak Struk
                </flux:button>
            </div>
        </div>
    @endif
</flux:modal>

<script>
    async function printReceipt() {
        const mode = localStorage.getItem('usb_receipt_printer_mode') || 'browser';
        
        if (mode === 'direct') {
            try {
                // Get the Livewire component instance of current page
                const voltEl = document.querySelector('[wire\\:id]');
                const wireId = voltEl ? voltEl.getAttribute('wire:id') : null;
                
                if (wireId && window.Livewire) {
                    const wireComponent = window.Livewire.find(wireId);
                    const sale = wireComponent ? wireComponent.get('completedSale') : null;
                    
                    if (sale) {
                        // Add cashier name if not present
                        if (!sale.cashier_name) {
                            sale.cashier_name = "{{ auth()->user()->name }}";
                        }
                        await window.printReceiptUSB(sale);
                        window.Livewire.dispatch('toast', { text: `Struk transaksi ${sale.invoice_number} berhasil dicetak via USB.`, variant: 'success' });
                        return;
                    }
                }
                throw new Error("Data transaksi tidak ditemukan.");
            } catch (err) {
                console.error("Direct USB printing failed:", err);
                if (window.Livewire) {
                    window.Livewire.dispatch('toast', { text: `Gagal cetak USB: ${err.message}. Mengalihkan ke cetak browser...`, variant: 'danger' });
                }
                printReceiptViaBrowser();
            }
        } else {
            printReceiptViaBrowser();
        }
    }

    function printReceiptViaBrowser() {
        const printContent = document.getElementById('receipt-print-area').innerHTML;
        
        // Create a hidden iframe
        let iframe = document.getElementById('receipt-print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'receipt-print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
        }
        
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <html>
            <head>
                <title>Print Receipt</title>
                <style>
                    @page {
                        size: 58mm auto;
                        margin: 0;
                    }
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        width: 58mm;
                        margin: 0;
                        padding: 10px;
                        font-size: 12px;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                    }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .font-bold { font-weight: bold; }
                    .text-base { font-size: 14px; }
                    .text-xs { font-size: 11px; }
                    .mb-2 { margin-bottom: 8px; }
                    .mb-3 { margin-bottom: 12px; }
                    .mt-4 { margin-top: 16px; }
                    .pt-2 { padding-top: 8px; }
                    .pb-2 { padding-bottom: 8px; }
                    .w-full { width: 100%; }
                    .border-b { border-bottom: 1px dashed #000; }
                    .border-t { border-top: 1px dashed #000; }
                    .space-y-1 > * + * { margin-top: 4px; }
                    table { border-collapse: collapse; }
                </style>
            </head>
            <body>
                \${printContent}
                <script>
                    window.onload = function() {
                        window.print();
                    }
                <\/script>
            </body>
            </html>
        `);
        doc.close();
    }
</script>

<x-usb-printer-settings type="receipt" />
</div>
