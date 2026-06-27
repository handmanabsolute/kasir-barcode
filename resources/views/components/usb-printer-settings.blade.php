@props(['type' => 'receipt'])

<flux:modal name="usb-printer-modal" class="max-w-md">
    <div x-data="usbPrinterSettings()" class="space-y-6">
        <div>
            <flux:heading size="lg" class="flex items-center gap-2">
                <flux:icon.printer class="h-6 w-6 text-zinc-600 dark:text-zinc-400" />
                <span x-text="type === 'barcode' ? 'Pengaturan Printer Barcode' : 'Pengaturan Printer Struk'"></span>
            </flux:heading>
            <flux:text class="mt-1" x-text="type === 'barcode' ? 'Hubungkan printer barcode atau alat scan USB untuk mencetak label.' : 'Hubungkan printer struk thermal USB untuk mencetak struk belanja.'"></flux:text>
        </div>

        <!-- Browser Support Warning -->
        <template x-if="!supportedUSB">
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs flex gap-2">
                <flux:icon.exclamation-triangle class="h-4 w-4 shrink-0 mt-0.5" />
                <div>
                    <strong>Peringatan:</strong> Browser Anda tidak mendukung WebUSB. Pencetakan otomatis dialihkan menggunakan dialog browser (window.print). Gunakan Google Chrome atau Microsoft Edge untuk dukungan USB penuh.
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Connection Status -->
            <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Status Perangkat:</span>
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
                                ID: <span class="font-mono text-blue-600 dark:text-blue-400">0x<span x-text="Number(vendorId).toString(16).toUpperCase().padStart(4, '0')"></span>:0x<span x-text="Number(productId).toString(16).toUpperCase().padStart(4, '0')"></span></span>
                            </div>
                            <template x-if="printerVerified">
                                <div class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                                    <flux:icon.check-circle class="h-3 w-3" />
                                    <span>Perangkat siap digunakan</span>
                                </div>
                            </template>
                            <template x-if="!printerVerified && printerSelected">
                                <div class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 mt-1">
                                    <flux:icon.exclamation-triangle class="h-3 w-3" />
                                    <span>Perangkat tersimpan namun tidak terdeteksi. Klik "Deteksi Ulang".</span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!printerSelected">
                        <span class="text-zinc-500 dark:text-zinc-400 text-xs">Klik tombol di bawah untuk menghubungkan printer atau alat scan USB.</span>
                    </template>
                </div>

                <div class="flex flex-col gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <template x-if="!printerSelected">
                        <flux:button size="sm" variant="primary" class="w-full justify-center" @click="connectDevice()" x-bind:loading="connecting" x-bind:disabled="!supportedUSB">
                            Hubungkan Perangkat USB
                        </flux:button>
                    </template>
                    <template x-if="printerSelected">
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="danger" class="flex-1" @click="disconnectDevice()">
                                Putuskan
                            </flux:button>
                            <flux:button size="sm" variant="outline" class="flex-1" @click="detectAndReconnect()" x-bind:loading="reconnecting">
                                Deteksi Ulang
                            </flux:button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Paired Devices List -->
            <template x-if="!printerSelected && pairedDevices.length > 0">
                <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 space-y-2">
                    <h4 class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">Perangkat yang Pernah Terhubung</h4>
                    <template x-for="(device, index) in pairedDevices" :key="index">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700">
                            <div class="flex items-center gap-2 min-w-0">
                                <flux:icon.printer class="h-4 w-4 shrink-0 text-zinc-400" />
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate" x-text="device.name"></div>
                                    <div class="text-xs text-zinc-500">
                                        <span x-text="'ID: 0x' + Number(device.vendorId).toString(16).toUpperCase().padStart(4, '0') + ':0x' + Number(device.productId).toString(16).toUpperCase().padStart(4, '0')"></span>
                                    </div>
                                </div>
                            </div>
                            <flux:button size="xs" variant="ghost" @click="reconnectPairedDevice(device)" x-bind:loading="device.reconnecting">
                                Hubungkan
                            </flux:button>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Instructions -->
            <div class="p-3 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900 rounded-lg text-xs space-y-2 text-zinc-600 dark:text-zinc-300">
                <h4 class="font-semibold text-blue-800 dark:text-blue-400">Petunjuk:</h4>
                <ul class="list-disc pl-4 space-y-1.5 leading-relaxed">
                    <li>Colokkan printer atau alat scan barcode ke port USB komputer.</li>
                    <li>Klik <strong>"Hubungkan Perangkat USB"</strong> dan pilih perangkat dari dialog browser.</li>
                    <li>Untuk printer thermal: Jika tidak muncul di dialog, gunakan aplikasi <strong>Zadig</strong> untuk mengubah driver menjadi <em>WinUSB</em>.</li>
                    <li>Gunakan Google Chrome atau Microsoft Edge untuk dukungan USB penuh.</li>
                </ul>
            </div>

            <!-- Messages -->
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
            supportedUSB: 'usb' in navigator,
            printerSelected: false,
            printerVerified: false,
            printerName: '',
            vendorId: null,
            productId: null,
            connecting: false,
            reconnecting: false,
            errorMessage: '',
            successMessage: '',
            pairedDevices: [],
            type: '{{ $type }}',

            init() {
                this.loadConfig();
                this.scanPreviouslyPairedDevices();
                
                window.addEventListener('usb-printer-settings-refresh', () => {
                    this.loadConfig();
                    this.scanPreviouslyPairedDevices();
                });
            },

            async loadConfig() {
                const storedVendorId = localStorage.getItem('usb_' + this.type + '_printer_vendor_id');
                const storedProductId = localStorage.getItem('usb_' + this.type + '_printer_product_id');
                const storedName = localStorage.getItem('usb_' + this.type + '_printer_name');

                if (storedVendorId && storedProductId) {
                    this.printerSelected = true;
                    this.vendorId = storedVendorId;
                    this.productId = storedProductId;
                    this.printerName = storedName || 'Perangkat USB';
                    await this.verifyDeviceConnection();
                } else {
                    this.printerSelected = false;
                    this.printerVerified = false;
                    this.vendorId = null;
                    this.productId = null;
                    this.printerName = '';
                }
            },

            async verifyDeviceConnection() {
                try {
                    if (this.supportedUSB) {
                        const devices = await navigator.usb.getDevices();
                        const found = devices.some(d => 
                            d.vendorId === Number(this.vendorId) && 
                            d.productId === Number(this.productId)
                        );
                        this.printerVerified = found;
                    } else {
                        this.printerVerified = false;
                    }
                } catch (err) {
                    console.warn("Verifikasi perangkat gagal:", err);
                    this.printerVerified = false;
                }
            },

            scanPreviouslyPairedDevices() {
                this.pairedDevices = [];
                const prefixes = ['usb_receipt_printer_', 'usb_barcode_printer_'];
                const seen = new Set();
                
                for (const prefix of prefixes) {
                    const vid = localStorage.getItem(prefix + 'vendor_id');
                    const pid = localStorage.getItem(prefix + 'product_id');
                    const name = localStorage.getItem(prefix + 'name');
                    
                    if (vid && pid) {
                        const key = vid + ':' + pid;
                        if (!seen.has(key)) {
                            seen.add(key);
                            if (!(this.printerSelected && this.vendorId == vid && this.productId == pid)) {
                                this.pairedDevices.push({
                                    vendorId: vid,
                                    productId: pid,
                                    name: name || 'Perangkat USB',
                                    reconnecting: false
                                });
                            }
                        }
                    }
                }
            },

            async reconnectPairedDevice(device) {
                device.reconnecting = true;
                this.clearMessages();
                
                try {
                    localStorage.setItem('usb_' + this.type + '_printer_vendor_id', device.vendorId);
                    localStorage.setItem('usb_' + this.type + '_printer_product_id', device.productId);
                    localStorage.setItem('usb_' + this.type + '_printer_name', device.name);
                    localStorage.setItem('usb_' + this.type + '_printer_mode', 'direct');
                    
                    this.printerSelected = true;
                    this.vendorId = device.vendorId;
                    this.productId = device.productId;
                    this.printerName = device.name;
                    this.printerVerified = false;
                    this.pairedDevices = [];
                    
                    await this.verifyDeviceConnection();
                    
                    if (this.printerVerified) {
                        this.showSuccess(`Perangkat ${this.printerName} berhasil dihubungkan kembali.`);
                    } else {
                        this.showError(`Perangkat ${this.printerName} tidak ditemukan. Colokkan perangkat dan coba lagi.`);
                    }
                } catch (err) {
                    this.showError("Gagal menghubungkan ulang: " + err.message);
                } finally {
                    device.reconnecting = false;
                }
            },

            async connectDevice() {
                this.connecting = true;
                this.clearMessages();
                try {
                    const device = await navigator.usb.requestDevice({ filters: [] });
                    
                    await device.open();
                    if (device.configuration === null) {
                        await device.selectConfiguration(1);
                    }
                    await device.close();
                    
                    localStorage.setItem('usb_' + this.type + '_printer_vendor_id', device.vendorId);
                    localStorage.setItem('usb_' + this.type + '_printer_product_id', device.productId);
                    localStorage.setItem('usb_' + this.type + '_printer_name', device.productName || 'Perangkat USB');
                    localStorage.setItem('usb_' + this.type + '_printer_mode', 'direct');
                    
                    this.printerSelected = true;
                    this.printerVerified = true;
                    this.vendorId = device.vendorId;
                    this.productId = device.productId;
                    this.printerName = device.productName || 'Perangkat USB';
                    this.pairedDevices = [];

                    this.showSuccess(`${this.printerName} berhasil dihubungkan.`);
                } catch (err) {
                    console.error("Koneksi USB gagal:", err);
                    if (err.name !== 'NotFoundError') {
                        this.showError("Gagal menghubungkan: " + err.message);
                    }
                } finally {
                    this.connecting = false;
                }
            },

            async detectAndReconnect() {
                this.reconnecting = true;
                this.clearMessages();
                try {
                    if (this.supportedUSB) {
                        const devices = await navigator.usb.getDevices();
                        const found = devices.find(d => 
                            d.vendorId === Number(this.vendorId) && 
                            d.productId === Number(this.productId)
                        );
                        
                        if (found) {
                            await found.open();
                            if (found.configuration === null) {
                                await found.selectConfiguration(1);
                            }
                            await found.close();
                            this.printerVerified = true;
                            this.showSuccess(`${this.printerName} terdeteksi dan siap digunakan.`);
                        } else {
                            const newDevice = await navigator.usb.requestDevice({
                                filters: [{ 
                                    vendorId: Number(this.vendorId), 
                                    productId: Number(this.productId) 
                                }]
                            });
                            await newDevice.open();
                            if (newDevice.configuration === null) {
                                await newDevice.selectConfiguration(1);
                            }
                            await newDevice.close();
                            this.printerVerified = true;
                            this.showSuccess(`${this.printerName} terdeteksi dan siap digunakan.`);
                        }
                    } else {
                        this.showError("WebUSB tidak didukung oleh browser ini.");
                    }
                } catch (err) {
                    console.error("Deteksi ulang gagal:", err);
                    if (err.name !== 'NotFoundError') {
                        this.showError("Deteksi ulang gagal: " + err.message);
                    }
                } finally {
                    this.reconnecting = false;
                }
            },

            disconnectDevice() {
                localStorage.removeItem('usb_' + this.type + '_printer_vendor_id');
                localStorage.removeItem('usb_' + this.type + '_printer_product_id');
                localStorage.removeItem('usb_' + this.type + '_printer_name');
                localStorage.removeItem('usb_' + this.type + '_printer_mode');
                
                this.printerSelected = false;
                this.printerVerified = false;
                this.vendorId = null;
                this.productId = null;
                this.printerName = '';
                
                this.showSuccess("Koneksi perangkat berhasil diputuskan.");
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

    // Global APIs triggered by pages
    window.showUsbPrinterSettings = function() {
        window.dispatchEvent(new CustomEvent('usb-printer-settings-refresh'));
    };

    window.printReceiptUSB = async function(sale) {
        const vendorId = localStorage.getItem('usb_receipt_printer_vendor_id');
        const productId = localStorage.getItem('usb_receipt_printer_product_id');
        
        if (!vendorId || !productId) {
            throw new Error("Printer struk belum dihubungkan. Silakan atur terlebih dahulu.");
        }

        const bytes = encodeESCPOSTransaction(sale);
        await writeToUSBPrinter(vendorId, productId, bytes);
    };

    window.printBarcodeUSB = async function(productName, barcodeValue) {
        const vendorId = localStorage.getItem('usb_barcode_printer_vendor_id');
        const productId = localStorage.getItem('usb_barcode_printer_product_id');
        
        if (!vendorId || !productId) {
            throw new Error("Printer barcode belum dihubungkan. Silakan atur terlebih dahulu.");
        }

        const bytes = encodeTSPLBarcode(productName, barcodeValue);
        await writeToUSBPrinter(vendorId, productId, bytes);
    };
</script>
