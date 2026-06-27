@props(['type' => 'receipt'])

<flux:modal name="usb-printer-modal" class="max-w-md">
    <div x-data="usbPrinterSettings()" class="space-y-6">
        <div>
            <flux:heading size="lg" class="flex items-center gap-2">
                <flux:icon.printer class="h-6 w-6 text-zinc-600 dark:text-zinc-400" />
                <span x-text="type === 'barcode' ? 'Pengaturan Printer Barcode' : 'Pengaturan Printer Struk'"></span>
            </flux:heading>
            <flux:text class="mt-1" x-text="type === 'barcode' ? 'Pilih perangkat USB printer barcode (label) yang terhubung untuk mencetak barcode.' : 'Pilih perangkat USB printer struk (thermal) yang terhubung untuk mencetak struk belanja.'"></flux:text>
        </div>

        <!-- Browser Support Warning -->
        <template x-if="!supported && !supportedSerial">
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs flex gap-2">
                <flux:icon.exclamation-triangle class="h-4 w-4 shrink-0 mt-0.5" />
                <div>
                    <strong>Peringatan:</strong> Browser Anda tidak mendukung WebUSB atau Web Serial. Pencetakan otomatis dialihkan menggunakan dialog browser (window.print). Gunakan Google Chrome atau Microsoft Edge untuk dukungan USB/Serial penuh.
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Connection Status -->
            <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Status Printer:</span>
                    <template x-if="printerSelected">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Terhubung
                        </span>
                    </template>
                    <template x-if="!printerSelected">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span> Belum Terhubung
                        </span>
                    </template>
                </div>

                <div class="text-sm">
                    <template x-if="printerSelected">
                        <div class="space-y-1.5">
                            <div class="font-medium text-zinc-800 dark:text-zinc-200" x-text="printerName"></div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                Mode: <span class="font-semibold uppercase text-blue-600 dark:text-blue-400" x-text="printerMode === 'serial' ? 'Serial / COM' : 'USB (WebUSB)'"></span>
                                <template x-if="printerMode === 'direct'">
                                    <span>(Vendor: <span x-text="'0x' + Number(vendorId).toString(16).toUpperCase()"></span> | Prod: <span x-text="'0x' + Number(productId).toString(16).toUpperCase()"></span>)</span>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="!printerSelected">
                        <span class="text-zinc-500 dark:text-zinc-400 text-xs">Silakan hubungkan printer USB/Serial Anda menggunakan salah satu metode di bawah.</span>
                    </template>
                </div>

                <div class="flex flex-col gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <template x-if="!printerSelected">
                        <div class="space-y-2 w-full">
                            <flux:button size="sm" variant="primary" class="w-full justify-center" @click="connectDevice()" x-bind:loading="connecting" :disabled="!supported">
                                Hubungkan via USB (WebUSB)
                            </flux:button>
                            <flux:button size="sm" variant="outline" class="w-full justify-center" @click="connectSerialDevice()" x-bind:loading="connecting" :disabled="!supportedSerial">
                                Hubungkan via Serial / COM (Web Serial)
                            </flux:button>
                        </div>
                    </template>
                    <template x-if="printerSelected">
                        <flux:button size="sm" variant="danger" class="w-full" @click="disconnectDevice()">
                            Putuskan Hubungan Printer
                        </flux:button>
                    </template>
                </div>
            </div>

            <!-- Explanatory Instructions -->
            <div class="p-3 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900 rounded-lg text-xs space-y-2 text-zinc-600 dark:text-zinc-300">
                <h4 class="font-semibold text-blue-800 dark:text-blue-400">Petunjuk Koneksi Printer USB/Serial:</h4>
                <ul class="list-disc pl-4 space-y-1.5 leading-relaxed">
                    <li><strong>Metode Serial / COM (Sangat Direkomendasikan)</strong>: Sangat cocok untuk printer thermal USB di sistem Windows. Printer dapat langsung dideteksi tanpa perlu mengubah driver printer bawaan Windows.</li>
                    <li><strong>Metode USB (WebUSB)</strong>: Untuk deteksi langsung USB. Jika printer tidak muncul di Windows, gunakan aplikasi <strong>Zadig</strong> untuk mengubah driver printer USB Anda menjadi <em>WinUSB</em>.</li>
                    <li>Pastikan Anda menggunakan Google Chrome atau Microsoft Edge untuk dukungan printer USB/Serial secara penuh.</li>
                </ul>
            </div>

            <!-- Action Message / Error / Success Feedback -->
            <div x-show="errorMessage" class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-xs flex gap-2" x-cloak>
                <flux:icon.exclamation-circle class="h-4 w-4 shrink-0" />
                <span x-text="errorMessage"></span>
            </div>
            <div x-show="successMessage" class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-xs flex gap-2" x-cloak>
                <flux:icon.check-circle class="h-4 w-4 shrink-0" />
                <span x-text="successMessage"></span>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:modal.close>
                <flux:button variant="ghost">Tutup</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('usbPrinterSettings', () => ({
            supported: 'usb' in navigator,
            supportedSerial: 'serial' in navigator,
            printerSelected: false,
            printerName: '',
            vendorId: null,
            productId: null,
            printerMode: '',
            connecting: false,
            errorMessage: '',
            successMessage: '',
            type: '{{ $type }}',

            init() {
                this.loadConfig();
                
                window.addEventListener('usb-printer-settings-refresh', () => {
                    this.loadConfig();
                });
            },

            loadConfig() {
                const storedVendorId = localStorage.getItem('usb_' + this.type + '_printer_vendor_id');
                const storedProductId = localStorage.getItem('usb_' + this.type + '_printer_product_id');
                const storedName = localStorage.getItem('usb_' + this.type + '_printer_name');
                const storedMode = localStorage.getItem('usb_' + this.type + '_printer_mode');

                if (storedVendorId && storedProductId) {
                    this.printerSelected = true;
                    this.vendorId = storedVendorId;
                    this.productId = storedProductId;
                    this.printerName = storedName || 'Printer USB Terdaftar';
                    this.printerMode = storedMode || 'direct';
                } else {
                    this.printerSelected = false;
                    this.vendorId = null;
                    this.productId = null;
                    this.printerName = '';
                    this.printerMode = '';
                }
            },

            async connectDevice() {
                this.connecting = true;
                this.clearMessages();
                try {
                    const device = await navigator.usb.requestDevice({ filters: [] });
                    localStorage.setItem('usb_' + this.type + '_printer_vendor_id', device.vendorId);
                    localStorage.setItem('usb_' + this.type + '_printer_product_id', device.productId);
                    localStorage.setItem('usb_' + this.type + '_printer_name', device.productName || 'USB Printer');
                    localStorage.setItem('usb_' + this.type + '_printer_mode', 'direct');
                    
                    this.printerSelected = true;
                    this.vendorId = device.vendorId;
                    this.productId = device.productId;
                    this.printerName = device.productName || 'USB Printer';
                    this.printerMode = 'direct';

                    this.showSuccess(`Printer ${this.printerName} berhasil dihubungkan.`);
                } catch (err) {
                    console.error("USB Connection error:", err);
                    if (err.name !== 'NotFoundError') {
                        this.showError("Gagal menghubungkan printer: " + err.message);
                    }
                } finally {
                    this.connecting = false;
                }
            },

            async connectSerialDevice() {
                this.connecting = true;
                this.clearMessages();
                try {
                    if (!('serial' in navigator)) {
                        throw new Error("Browser Anda tidak mendukung Web Serial.");
                    }
                    const port = await navigator.serial.requestPort();
                    const info = port.getInfo();
                    
                    // Web Serial products might have USB vendor/product IDs or defaults
                    const vendorId = info.usbVendorId || 9999;
                    const productId = info.usbProductId || 9999;
                    
                    localStorage.setItem('usb_' + this.type + '_printer_vendor_id', vendorId);
                    localStorage.setItem('usb_' + this.type + '_printer_product_id', productId);
                    localStorage.setItem('usb_' + this.type + '_printer_name', 'Serial/COM Port Printer');
                    localStorage.setItem('usb_' + this.type + '_printer_mode', 'serial');
                    
                    this.printerSelected = true;
                    this.vendorId = vendorId;
                    this.productId = productId;
                    this.printerName = 'Serial/COM Port Printer';
                    this.printerMode = 'serial';

                    this.showSuccess("Printer Serial/COM berhasil dihubungkan.");
                } catch (err) {
                    console.error("Serial connection error:", err);
                    if (err.name !== 'NotFoundError') {
                        this.showError("Gagal menghubungkan printer serial: " + err.message);
                    }
                } finally {
                    this.connecting = false;
                }
            },

            disconnectDevice() {
                localStorage.removeItem('usb_' + this.type + '_printer_vendor_id');
                localStorage.removeItem('usb_' + this.type + '_printer_product_id');
                localStorage.removeItem('usb_' + this.type + '_printer_name');
                localStorage.removeItem('usb_' + this.type + '_printer_mode');
                
                this.printerSelected = false;
                this.vendorId = null;
                this.productId = null;
                this.printerName = '';
                this.printerMode = '';
                
                this.showSuccess("Koneksi printer berhasil diputuskan.");
            },

            showError(msg) {
                this.errorMessage = msg;
                setTimeout(() => { this.errorMessage = ''; }, 6000);
            },

            showSuccess(msg) {
                this.successMessage = msg;
                setTimeout(() => { this.successMessage = ''; }, 4000);
            },

            clearMessages() {
                this.errorMessage = '';
                this.successMessage = '';
            }
        }));
    });

    // Helper functions for formatting & printing
    function formatRupiahJS(amount) {
        return "Rp " + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(amount);
    }

    // Direct USB printing core logic
    async function writeToUSBPrinter(vendorId, productId, dataArray) {
        let device;
        try {
            const devices = await navigator.usb.getDevices();
            device = devices.find(d => d.vendorId === Number(vendorId) && d.productId === Number(productId));
            
            if (!device) {
                device = await navigator.usb.requestDevice({
                    filters: [{ vendorId: Number(vendorId), productId: Number(productId) }]
                });
            }

            await device.open();
            
            if (device.configuration === null) {
                await device.selectConfiguration(1);
            }
            
            let interfaceNumber = 0;
            let endpointNumber = 1;
            let found = false;
            
            for (const config of device.configurations) {
                for (const iface of config.interfaces) {
                    for (const alternate of iface.alternates) {
                        if (alternate.interfaceClass === 7 || alternate.interfaceClass === 255 || alternate.interfaceClass === 0) {
                            const outEndpoint = alternate.endpoints.find(e => e.direction === 'out' && e.type === 'bulk');
                            if (outEndpoint) {
                                interfaceNumber = iface.interfaceNumber;
                                endpointNumber = outEndpoint.endpointNumber;
                                found = true;
                                break;
                            }
                        }
                    }
                    if (found) break;
                }
                if (found) break;
            }

            await device.claimInterface(interfaceNumber);
            await device.transferOut(endpointNumber, dataArray);
            await device.releaseInterface(interfaceNumber);
            await device.close();
        } catch (error) {
            console.error("WebUSB execution failed:", error);
            throw new Error(error.message || "Pastikan printer menyala dan terhubung melalui USB.");
        }
    }

    // Direct Serial/COM printing core logic
    async function writeToSerialPrinter(vendorId, productId, dataArray) {
        let port;
        try {
            const ports = await navigator.serial.getPorts();
            // Try to find by vendor/product info if available
            port = ports.find(p => {
                const info = p.getInfo();
                return info.usbVendorId === Number(vendorId) && info.usbProductId === Number(productId);
            });
            
            if (!port) {
                port = await navigator.serial.requestPort();
            }
            
            await port.open({ baudRate: 9600 });
            const writer = port.writable.getWriter();
            await writer.write(dataArray);
            writer.releaseLock();
            await port.close();
        } catch (error) {
            console.error("WebSerial execution failed:", error);
            throw new Error(error.message || "Pastikan printer serial menyala dan terhubung.");
        }
    }

    // ESC/POS encoder for Receipt transaction
    function encodeESCPOSTransaction(sale) {
        const encoder = new TextEncoder();
        let bytes = [];
        const add = (arr) => bytes.push(...arr);
        const addStr = (str) => add(encoder.encode(str));
        
        add([0x1B, 0x40]); // Init
        add([0x1B, 0x61, 0x01]); // Align Center
        
        add([0x1B, 0x45, 0x01]);
        add([0x1D, 0x21, 0x11]);
        addStr("KASIR BARCODE\n");
        add([0x1D, 0x21, 0x00]); // Normal
        add([0x1B, 0x45, 0x00]);
        addStr("\n");
        
        addStr(`${sale.sold_at_formatted}\n`);
        addStr(`Invoice: ${sale.invoice_number}\n`);
        addStr(`Kasir: ${sale.cashier_name || 'Kasir'}\n`);
        addStr("--------------------------------\n");
        
        add([0x1B, 0x61, 0x00]); // Left align
        
        sale.items.forEach(item => {
            addStr(`${item.product_name || item.product?.name}\n`);
            let qtyPrice = `  ${item.quantity} x ${formatRupiahJS(item.unit_price)}`;
            let total = formatRupiahJS(item.line_total);
            let spaces = 32 - qtyPrice.length - total.length;
            if (spaces < 1) spaces = 1;
            addStr(qtyPrice + " ".repeat(spaces) + total + "\n");
        });
        
        add([0x1B, 0x61, 0x01]);
        addStr("--------------------------------\n");
        add([0x1B, 0x61, 0x02]); // Right align
        
        const addTotalLine = (label, val, isBold = false) => {
            if (isBold) add([0x1B, 0x45, 0x01]);
            let line = `${label}: ${val}`;
            addStr(line + "\n");
            if (isBold) add([0x1B, 0x45, 0x00]);
        };
        
        addTotalLine("Total", sale.total_formatted || formatRupiahJS(sale.total), true);
        addTotalLine("Bayar", sale.paid_amount_formatted || formatRupiahJS(sale.paid_amount));
        addTotalLine("Kembali", sale.change_amount_formatted || formatRupiahJS(sale.change_amount), true);
        
        add([0x1B, 0x61, 0x01]);
        addStr("\nTerima Kasih\nSelamat Belanja Kembali!\n\n\n\n");
        add([0x1D, 0x56, 0x42, 0x00]); // Feed & Cut
        
        return new Uint8Array(bytes);
    }

    // TSPL encoder for Product Barcode Label (Default 40mm x 30mm)
    function encodeTSPLBarcode(productName, barcodeValue) {
        const encoder = new TextEncoder();
        let tspl = 
            "SIZE 40 mm, 30 mm\r\n" +
            "GAP 2 mm, 0\r\n" +
            "DIRECTION 1\r\n" +
            "REFERENCE 0,0\r\n" +
            "OFFSET 0 mm\r\n" +
            "SET PEEL OFF\r\n" +
            "CLS\r\n" +
            `TEXT 10,15,"3",0,1,1,"${productName.substring(0, 22)}"\r\n` +
            `BARCODE 10,45,"128",50,1,0,2,2,"${barcodeValue}"\r\n` +
            "PRINT 1,1\r\n";
            
        return encoder.encode(tspl);
    }

    // ESC/POS encoder for Product Barcode (Thermal Receipt)
    function encodeESCPOSTransactionBarcode(productName, barcodeValue) {
        const encoder = new TextEncoder();
        let bytes = [];
        const add = (arr) => bytes.push(...arr);
        const addStr = (str) => add(encoder.encode(str));
        
        add([0x1B, 0x40]); // Init
        add([0x1B, 0x61, 0x01]); // Align Center
        
        add([0x1B, 0x45, 0x01]);
        addStr(`${productName}\n\n`);
        add([0x1B, 0x45, 0x00]);
        
        add([0x1D, 0x6B, 0x49]); 
        let len = barcodeValue.length;
        add([len + 2]); 
        add([0x7B, 0x42]); 
        addStr(barcodeValue);
        
        addStr("\n\n\n\n");
        add([0x1D, 0x56, 0x42, 0x00]); // Feed & Cut
        
        return new Uint8Array(bytes);
    }

    // Global APIs triggered by pages
    window.showUsbPrinterSettings = function() {
        window.dispatchEvent(new CustomEvent('usb-printer-settings-refresh'));
    };

    window.printReceiptUSB = async function(sale) {
        const vendorId = localStorage.getItem('usb_receipt_printer_vendor_id');
        const productId = localStorage.getItem('usb_receipt_printer_product_id');
        const mode = localStorage.getItem('usb_receipt_printer_mode') || 'direct';
        
        if (!vendorId || !productId) {
            throw new Error("Printer struk belum dihubungkan. Silakan atur terlebih dahulu.");
        }

        const bytes = encodeESCPOSTransaction(sale);
        if (mode === 'serial') {
            await writeToSerialPrinter(vendorId, productId, bytes);
        } else {
            await writeToUSBPrinter(vendorId, productId, bytes);
        }
    };

    window.printBarcodeUSB = async function(productName, barcodeValue) {
        const vendorId = localStorage.getItem('usb_barcode_printer_vendor_id');
        const productId = localStorage.getItem('usb_barcode_printer_product_id');
        const mode = localStorage.getItem('usb_barcode_printer_mode') || 'direct';
        
        if (!vendorId || !productId) {
            throw new Error("Printer barcode belum dihubungkan. Silakan atur terlebih dahulu.");
        }

        const bytes = encodeTSPLBarcode(productName, barcodeValue);
        if (mode === 'serial') {
            await writeToSerialPrinter(vendorId, productId, bytes);
        } else {
            await writeToUSBPrinter(vendorId, productId, bytes);
        }
    };
</script>
