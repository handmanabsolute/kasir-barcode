<?php

use App\Models\Category;
use App\Models\Product;
use App\Support\Money;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Produk')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public string $name = '';

    public string $barcode = '';

    public ?int $category_id = null;

    public int $stock = 0;

    public int $cost_price = 0;

    public int $sell_price = 0;

    public ?int $editingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($this->editingId)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'cost_price' => ['required', 'integer', 'min:0'],
            'sell_price' => ['required', 'integer', 'min:0'],
        ]);

        if ($validated['sell_price'] < $validated['cost_price']) {
            $this->addError('sell_price', 'Harga jual tidak boleh lebih kecil dari harga modal.');

            return;
        }

        if ($this->editingId) {
            Product::query()->whereKey($this->editingId)->update($validated);
            Flux::toast(variant: 'success', text: 'Produk berhasil diperbarui.');
        } else {
            Product::query()->create([
                ...$validated,
                'is_active' => true,
            ]);
            Flux::toast(variant: 'success', text: 'Produk berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function edit(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->barcode = $product->barcode;
        $this->category_id = $product->category_id;
        $this->stock = $product->stock;
        $this->cost_price = $product->cost_price;
        $this->sell_price = $product->sell_price;
    }

    public function delete(int $productId): void
    {
        Product::query()->whereKey($productId)->delete();

        if ($this->editingId === $productId) {
            $this->resetForm();
        }

        Flux::toast(variant: 'success', text: 'Produk berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'barcode', 'category_id', 'stock', 'cost_price', 'sell_price', 'editingId']);
        $this->resetValidation();
    }

    public function with(): array
    {
        return [
            'products' => Product::query()
                ->with('category')
                ->when($this->search !== '', function ($query): void {
                    $query->where(function ($query): void {
                        $query->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('barcode', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest()
                ->paginate(10),
            'categories' => Category::query()->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="flex w-full flex-col gap-6" x-data="{
    init() {
        window.addEventListener('barcode-scanned', (e) => {
            this.$wire.set('barcode', e.detail);
        });
    }
}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">Produk</flux:heading>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Scanner USB: Aktif
                </span>
            </div>
            <flux:text class="mt-1">Kelola produk, stok, harga, dan barcode. Scanner USB akan mengisi field barcode otomatis.</flux:text>
        </div>

        <div class="flex gap-2 w-full lg:max-w-md">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari nama atau barcode..." />
            </div>
            <flux:modal.trigger name="usb-printer-modal">
                <flux:button icon="printer" onclick="window.showUsbPrinterSettings()" tooltip="Pengaturan Printer USB" />
            </flux:modal.trigger>
        </div>
    </div>


    <div class="grid gap-6 xl:grid-cols-[380px_minmax(0,1fr)]">
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ $editingId ? 'Edit Produk' : 'Tambah Produk' }}</flux:heading>

            <form wire:submit="save" class="mt-4 flex flex-col gap-4">
                <flux:input wire:model="name" label="Nama produk" placeholder="Contoh: Air Mineral 600ml" required />

                <div>
                    <flux:input
                        wire:model="barcode"
                        label="Barcode"
                        placeholder="Scan barcode atau ketik manual"
                        required
                        autofocus
                    />
                    <flux:text class="mt-1 text-xs text-zinc-500">Fokuskan field ini, lalu scan dengan alat barcode USB.</flux:text>
                </div>

                <flux:select wire:model="category_id" label="Kategori" placeholder="Pilih kategori">
                    @foreach ($categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="stock" type="number" min="0" label="Stok" required />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="cost_price" type="number" min="0" label="Harga modal (Rp)" required />
                    <flux:input wire:model="sell_price" type="number" min="0" label="Harga jual (Rp)" required />
                </div>

                @if ($cost_price > 0 || $sell_price > 0)
                    <flux:text class="text-sm text-zinc-500">
                        Estimasi laba per unit: {{ Money::format(max(0, $sell_price - $cost_price)) }}
                    </flux:text>
                @endif

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Simpan perubahan' : 'Tambah produk' }}
                    </flux:button>

                    @if ($editingId)
                        <flux:button type="button" variant="ghost" wire:click="resetForm">Batal</flux:button>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 pb-4 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Produk</flux:table.column>
                    <flux:table.column>Barcode</flux:table.column>
                    <flux:table.column>Kategori</flux:table.column>
                    <flux:table.column>Stok</flux:table.column>
                    <flux:table.column>Harga</flux:table.column>
                    <flux:table.column>Laba/unit</flux:table.column>
                    <flux:table.column class="w-40">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($products as $product)
                        <flux:table.row wire:key="product-{{ $product->id }}">
                            <flux:table.cell>
                                <div class="font-medium">{{ $product->name }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $product->barcode }}</flux:table.cell>
                            <flux:table.cell>{{ $product->category?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $product->stock }}</flux:table.cell>
                            <flux:table.cell>
                                <div>{{ Money::format($product->sell_price) }}</div>
                                <flux:text class="text-xs">Modal {{ Money::format($product->cost_price) }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>{{ Money::format($product->profitMargin()) }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="ghost" onclick="printProductBarcode('{{ addslashes($product->name) }}', '{{ $product->barcode }}')">Cetak</flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="edit({{ $product->id }})">Edit</flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete({{ $product->id }})" wire:confirm="Hapus produk ini?">Hapus</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7">Produk tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-6 border-t border-neutral-200 pt-4 dark:border-neutral-700">
                {{ $products->links() }}
            </div>
        </div>
    </div>

<x-usb-printer-settings type="barcode" />

<script>
    function generateBarcodeSVG(text) {
        // Safe Code 39 encoder
        const cleanText = text.toUpperCase().replace(/[^A-Z0-9\-\.\ \$\/\+\%]/g, '');
        const formattedText = '*' + cleanText + '*';
        
        const Code39Map = {
            '0': '111221211', '1': '211211112', '2': '112211112', '3': '212211111',
            '4': '111221112', '5': '211221111', '6': '112221111', '7': '111211212',
            '8': '211211211', '9': '112211211', 'A': '211112112', 'B': '112112112',
            'C': '212112111', 'D': '111122112', 'E': '211122111', 'F': '112122111',
            'G': '111112212', 'H': '211112211', 'I': '112112211', 'J': '111122211',
            'K': '211111122', 'L': '112111122', 'M': '212111121', 'N': '111121122',
            'O': '211121121', 'P': '112121121', 'Q': '111111222', 'R': '211111221',
            'S': '112111221', 'T': '111121221', 'U': '221111112', 'V': '122111112',
            'W': '222111111', 'X': '121121112', 'Y': '221121111', 'Z': '122121111',
            '-': '121111212', '.': '221111211', ' ': '122111211', '*': '121121211',
            '$': '121212111', '/': '121211121', '+': '121112121', '%': '111212121'
        };

        let narrowWidth = 1.5;
        let wideWidth = 3.5;
        let barHeight = 45;
        
        let x = 0;
        let svgContent = '';
        
        for (let i = 0; i < formattedText.length; i++) {
            let char = formattedText[i];
            let pattern = Code39Map[char];
            if (!pattern) continue;
            
            for (let j = 0; j < pattern.length; j++) {
                let width = (pattern[j] === '2') ? wideWidth : narrowWidth;
                let isBar = (j % 2 === 0);
                
                if (isBar) {
                    svgContent += `<rect x="${x}" y="0" width="${width}" height="${barHeight}" fill="black" />`;
                }
                x += width;
            }
            x += narrowWidth;
        }
        
        return `<svg width="100%" height="100%" viewBox="0 0 ${x} ${barHeight + 15}" xmlns="http://www.w3.org/2000/svg">
            ${svgContent}
            <text x="${x/2}" y="${barHeight + 12}" font-family="monospace" font-size="9" font-weight="bold" text-anchor="middle" fill="black">${cleanText}</text>
        </svg>`;
    }

    async function printProductBarcode(productName, barcodeValue) {
        const mode = localStorage.getItem('usb_barcode_printer_mode') || 'browser';
        
        if (mode === 'direct') {
            try {
                await window.printBarcodeUSB(productName, barcodeValue);
                if (window.Livewire) {
                    window.Livewire.dispatch('toast', { text: `Barcode ${productName} berhasil dikirim ke printer USB.`, variant: 'success' });
                }
            } catch (err) {
                console.error("Direct printing failed:", err);
                if (window.Livewire) {
                    window.Livewire.dispatch('toast', { text: `Gagal cetak USB: ${err.message}. Mengalihkan ke cetak browser...`, variant: 'danger' });
                }
                // Fallback to browser print if direct failed
                printBarcodeViaBrowser(productName, barcodeValue);
            }
        } else {
            printBarcodeViaBrowser(productName, barcodeValue);
        }
    }

    function printBarcodeViaBrowser(productName, barcodeValue) {
        let iframe = document.getElementById('barcode-print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'barcode-print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
        }
        
        const barcodeSVG = generateBarcodeSVG(barcodeValue);
        
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <html>
            <head>
                <title>Cetak Barcode</title>
                <style>
                    @page {
                        size: 50mm 30mm;
                        margin: 0;
                    }
                    body {
                        font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                        width: 50mm;
                        height: 30mm;
                        margin: 0;
                        padding: 4px;
                        box-sizing: border-box;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        background: #fff;
                        color: #000;
                    }
                    .title {
                        font-size: 9px;
                        font-weight: bold;
                        text-align: center;
                        margin-bottom: 3px;
                        width: 100%;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    .barcode-container {
                        width: 100%;
                        height: 18mm;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                </style>
            </head>
            <body>
                <div class="title">\${productName}</div>
                <div class="barcode-container">
                    \${barcodeSVG}
                </div>
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
</div>
