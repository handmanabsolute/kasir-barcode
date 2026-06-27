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
                                    <span>Perangkat tersimpan namun tidak terdeteksi secara fisik. Colokkan perangkat dan klik "Deteksi Ulang".</span>
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
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" class="flex-1 justify-center" @click="connectDevice()" x-bind:loading="connecting" x-bind:disabled="!supportedUSB">
                                Hubungkan Perangkat USB
                            </flux:button>
                            <flux:button size="sm" variant="outline" class="flex-1 justify-center" @click="scanAvailableDevices()" x-bind:loading="scanning" x-bind:disabled="!supportedUSB">
                                <template x-if="!scanning">Scan Perangkat</template>
                                <template x-if="scanning">Memindai...</template>
                            </flux:button>
                        </div>
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

            <!-- Available Devices (auto-detected via WebUSB) -->
            <template x-if="!printerSelected && availableDevices.length > 0">
                <div class="p-3 rounded-xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50/50 dark:bg-emerald-950/20 space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Perangkat Terdeteksi
                        </h4>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium" x-text="availableDevices.length + ' perangkat'"></span>
                    </div>
                    <template x-for="(device, index) in availableDevices" :key="index">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-zinc-800 border border-emerald-100 dark:border-emerald-800">
                            <div class="flex items-center gap-2 min-w-0">
                                <flux:icon.printer class="h-4 w-4 shrink-0 text-emerald-500" />
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate" x-text="getDeviceDisplayName(device)"></div>
                                    <div class="text-xs text-zinc-500">
                                        <span x-text="'ID: 0x' + Number(device.vendorId).toString(16).toUpperCase().padStart(4, '0') + ':0x' + Number(device.productId).toString(16).toUpperCase().padStart(4, '0')"></span>
                                    </div>
                                </div>
                            </div>
                            <flux:button size="xs" variant="primary" @click="useAvailableDevice(device)">
                                Pilih
                            </flux:button>
                        </div>
                    </template>
                </div>
            </template>

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
                    <li>Klik <strong>"Scan Perangkat"</strong> untuk mendeteksi perangkat USB yang sudah diizinkan sebelumnya.</li>
                    <li>Klik <strong>"Hubungkan Perangkat USB"</strong> dan pilih perangkat dari dialog browser untuk pertama kali.</li>
                    <li>Untuk printer thermal: Jika tidak muncul di dialog browser, gunakan aplikasi <strong>Zadig</strong> untuk mengubah driver perangkat menjadi <em>WinUSB</em>.</li>
                    <li>Gunakan Google Chrome atau Microsoft Edge untuk dukungan USB penuh.</li>
                    <li>Setelah terhubung, perangkat akan otomatis terdeteksi saat halaman dimuat ulang.</li>
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
            scanning: false,
            errorMessage: '',
            successMessage: '',
            pairedDevices: [],
            availableDevices: [],
            type: '{{ $type }}',

            init() {
                this.loadConfig();
                this.scanPreviouslyPairedDevices();
                this.scanAvailableDevices();

                // Listen for USB device connect/disconnect events (real-time detection)
                if (this.supportedUSB) {
                    navigator.usb.addEventListener('connect', (event) => {
                        console.log('USB device connected:', event.device);
                        this.scanAvailableDevices();
                    });
                    navigator.usb.addEventListener('disconnect', (event) => {
                        console.log('USB device disconnected:', event.device);
                        this.scanAvailableDevices();
                        if (this.printerSelected) {
                            this.verifyDeviceConnection();
                        }
                    });
                }
                
                window.addEventListener('usb-printer-settings-refresh', () => {
                    this.loadConfig();
                    this.scanPreviouslyPairedDevices();
                    this.scanAvailableDevices();
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
                    this.printerName = storedName || this.getStoredDeviceDisplayName();
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
                                const vidHex = Number(vid).toString(16).toUpperCase().padStart(4, '0');
                                const pidHex = Number(pid).toString(16).toUpperCase().padStart(4, '0');
                                const deviceKey = '0x' + vidHex + ':0x' + pidHex;
                                const knownName = KNOWN_USB_DEVICES[deviceKey];
                                const displayName = knownName || name || 'Perangkat USB (0x' + vidHex + ':0x' + pidHex + ')';
                                
                                this.pairedDevices.push({
                                    vendorId: vid,
                                    productId: pid,
                                    name: displayName,
                                    reconnecting: false
                                });
                            }
                        }
                    }
                }
            },

            async scanAvailableDevices() {
                if (!this.supportedUSB) return;
                
                this.scanning = true;
                try {
                    let devices = await navigator.usb.getDevices();
                    
                    // Try to open each device briefly to trigger string descriptor reading
                    // This helps browsers that lazily populate productName/manufacturerName
                    for (const d of devices) {
                        if (!d.productName && !d.manufacturerName) {
                            try {
                                await d.open();
                                // Just opening can trigger the browser to read descriptors
                                await d.close();
                            } catch (err) {
                                // Device might be in use, skip opening
                                console.warn('Could not open device ' + d.vendorId + ':' + d.productId + ' for name retrieval:', err);
                            }
                        }
                    }
                    
                    // Re-fetch devices after attempting to open them
                    devices = await navigator.usb.getDevices();
                    
                    // Filter out the currently selected printer
                    const filtered = devices.filter(d => 
                        !(this.printerSelected && 
                          d.vendorId === Number(this.vendorId) && 
                          d.productId === Number(this.productId))
                    );
                    
                    // Sort: named devices first, then by type
                    filtered.sort((a, b) => {
                        const nameA = getDeviceDisplayName(a);
                        const nameB = getDeviceDisplayName(b);
                        return nameA.localeCompare(nameB);
                    });
                    
                    this.availableDevices = filtered;
                } catch (err) {
                    console.warn("Gagal memindai perangkat USB:", err);
                } finally {
                    this.scanning = false;
                }
            },

            async useAvailableDevice(device) {
                this.clearMessages();
                this.connecting = true;
                
                try {
                    // Try to open and test the connection
                    await device.open();
                    if (device.configuration === null) {
                        await device.selectConfiguration(1);
                    }
                    await device.close();
                    
                    // Save device info to localStorage
                    localStorage.setItem('usb_' + this.type + '_printer_vendor_id', device.vendorId);
                    localStorage.setItem('usb_' + this.type + '_printer_product_id', device.productId);
                    localStorage.setItem('usb_' + this.type + '_printer_name', this.getBestDeviceName(device));
                    localStorage.setItem('usb_' + this.type + '_printer_mode', 'direct');
                    
                    this.printerSelected = true;
                    this.printerVerified = true;
                    this.vendorId = device.vendorId;
                    this.productId = device.productId;
                    this.printerName = this.getBestDeviceName(device);
                    this.availableDevices = [];
                    this.pairedDevices = [];
                    
                    this.showSuccess(`${this.printerName} berhasil dipilih dan siap digunakan.`);
                } catch (err) {
                    console.error("Gagal menggunakan perangkat:", err);
                    this.showError("Gagal menginisialisasi perangkat: " + (err.message || 'Perangkat tidak merespon. Pastikan driver WinUSB terinstall.'));
                } finally {
                    this.connecting = false;
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
                        this.showError(`Perangkat ${this.printerName} tidak ditemukan secara fisik. Colokkan perangkat dan coba lagi, atau klik "Scan Perangkat" untuk mendeteksi ulang.`);
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
                    localStorage.setItem('usb_' + this.type + '_printer_name', this.getBestDeviceName(device));
                    localStorage.setItem('usb_' + this.type + '_printer_mode', 'direct');
                    
                    this.printerSelected = true;
                    this.printerVerified = true;
                    this.vendorId = device.vendorId;
                    this.productId = device.productId;
                    this.printerName = this.getBestDeviceName(device);
                    this.availableDevices = [];
                    this.pairedDevices = [];

                    this.showSuccess(`${this.printerName} berhasil dihubungkan.`);
                } catch (err) {
                    console.error("Koneksi USB gagal:", err);
                    if (err.name === 'NotFoundError') {
                        // User cancelled the dialog — no error needed
                    } else if (err.message && err.message.includes('Access denied')) {
                        this.showError("Akses ditolak. Pastikan driver WinUSB terinstall menggunakan aplikasi Zadig untuk printer thermal.");
                    } else {
                        this.showError("Gagal menghubungkan: " + (err.message || 'Kesalahan tidak diketahui'));
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
                            // Try requesting just this specific device
                            try {
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
                            } catch (innerErr) {
                                if (innerErr.name === 'NotFoundError') {
                                    this.showError(`Perangkat ${this.printerName} tidak ditemukan. Pastikan: 1) Perangkat tercolok USB, 2) Driver WinUSB terinstall (gunakan Zadig), 3) Coba klik "Scan Perangkat" atau hubungkan ulang.`);
                                } else {
                                    throw innerErr;
                                }
                            }
                        }
                    } else {
                        this.showError("WebUSB tidak didukung oleh browser ini.");
                    }
                } catch (err) {
                    console.error("Deteksi ulang gagal:", err);
                    this.showError("Deteksi ulang gagal: " + (err.message || 'Kesalahan tidak diketahui'));
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
                
                // Re-scan available devices after disconnecting
                this.scanAvailableDevices();
                this.scanPreviouslyPairedDevices();
                
                this.showSuccess("Koneksi perangkat berhasil diputuskan.");
            },

            getBestDeviceName(device) {
                // Delegate to the global function which has all the lookup strategies
                return getDeviceDisplayName(device);
            },

            getStoredDeviceDisplayName() {
                const vid = this.vendorId ? Number(this.vendorId).toString(16).toUpperCase().padStart(4, '0') : '';
                const pid = this.productId ? Number(this.productId).toString(16).toUpperCase().padStart(4, '0') : '';
                const key = '0x' + vid + ':0x' + pid;
                
                if (KNOWN_USB_DEVICES[key]) return KNOWN_USB_DEVICES[key];
                
                if (vid && pid) {
                    return 'Perangkat USB (0x' + vid + ':0x' + pid + ')';
                }
                return 'Perangkat USB';
            },

            showError(msg) {
                this.errorMessage = msg;
                setTimeout(() => { this.errorMessage = ''; }, 8000);
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

    // ── Known USB Device Database ──
    // Maps vendorId:productId to known device names for common POS printers/scanners
    const KNOWN_USB_DEVICES = {
        // Epson
        '0x04b8:0x0202': 'Epson TM-T20 Receipt Printer',
        '0x04b8:0x0205': 'Epson TM-T20II Receipt Printer',
        '0x04b8:0x0207': 'Epson TM-T20III Receipt Printer',
        '0x04b8:0x0e15': 'Epson TM-T88 Receipt Printer',
        '0x04b8:0x0e28': 'Epson TM-T88V Receipt Printer',
        '0x04b8:0x0e2f': 'Epson TM-T88VI Receipt Printer',
        '0x04b8:0x0201': 'Epson TM-U220 Receipt Printer',
        '0x04b8:0x0e03': 'Epson TM-U220 Receipt Printer',
        '0x04b8:0x0e0b': 'Epson TM-T82 Receipt Printer',
        '0x04b8:0x0e12': 'Epson TM-T82II Receipt Printer',
        '0x04b8:0x0e27': 'Epson TM-m10 Receipt Printer',
        '0x04b8:0x0e2e': 'Epson TM-m30 Receipt Printer',
        '0x04b8:0x0e38': 'Epson TM-m30II Receipt Printer',
        '0x04b8:0x0e3b': 'Epson TM-m50 Receipt Printer',
        
        // Star Micronics
        '0x0519:0x0002': 'Star SP700 Receipt Printer',
        '0x0519:0x0201': 'Star TSP100 Receipt Printer',
        '0x0519:0x0202': 'Star TSP100III Receipt Printer',
        '0x0519:0x0206': 'Star TSP143 Receipt Printer',
        '0x0519:0x020b': 'Star TSP143III Receipt Printer',
        '0x0519:0x0301': 'Star mPOP Receipt Printer',
        '0x0519:0x0302': 'Star mPOP Barcode Scanner',
        
        // Xprinter / Xpres
        '0x1fc9:0x2016': 'Xprinter XP-58 Thermal Printer',
        '0x1fc9:0x2017': 'Xprinter XP-80 Thermal Printer',
        '0x1fc9:0x2018': 'Xprinter XP-76 Thermal Printer',
        '0x1fc9:0x2026': 'Xprinter XP-58IIH Thermal Printer',
        '0x1fc9:0x2028': 'Xprinter XP-80C Thermal Printer',
        
        // Chinese Generic Thermal Printers (common in Indonesia)
        '0x0416:0x5011': 'Thermal Printer 58mm (GP-58)',
        '0x0416:0x5020': 'Thermal Printer 80mm (GP-80)',
        '0x0416:0x5030': 'Thermal Printer 58mm (GP-58S)',
        '0x0416:0x5040': 'Thermal Printer 80mm (GP-80S)',
        '0x067b:0x2305': 'Thermal Printer 58mm (ProliNK)',
        '0x067b:0x2306': 'Thermal Printer 80mm (ProliNK)',
        '0x1a86:0x5584': 'Thermal Printer USB Adapter (CH340)',
        '0x10c4:0xea60': 'Thermal Printer USB Adapter (CP210x)',
        '0x0403:0x6001': 'Thermal Printer USB Adapter (FT232)',
        '0x0483:0x5740': 'Thermal Printer 58mm (STM32)',
        
        // Barcode Scanners
        '0x0c2e:0x0a00': 'Honeywell 1200g Barcode Scanner',
        '0x0c2e:0x0b00': 'Honeywell 1300g Barcode Scanner',
        '0x0c2e:0x0e00': 'Honeywell 1900 Barcode Scanner',
        '0x05f9:0x2202': 'Symbol LS2208 Barcode Scanner',
        '0x05f9:0x1202': 'Symbol LS1203 Barcode Scanner',
        '0x05f9:0x2000': 'Zebra DS2208 Barcode Scanner',
        '0x05f9:0x2001': 'Zebra DS2278 Barcode Scanner',
        '0x05f9:0x0100': 'Zebra DS9208 Barcode Scanner',
        '0x05f9:0x1402': 'Motorola Symbol Barcode Scanner',
        '0x0681:0x0001': 'Wasp Barcode Scanner',
        '0x0581:0x0200': 'Fujitsu Barcode Scanner',
        '0x0764:0x0002': 'CipherLab Barcode Scanner',
        '0x11f5:0x0001': 'Newland Barcode Scanner',
        '0x1915:0x0102': 'Newland NLS-HR15 Barcode Scanner',
        
        // Bixolon
        '0x1606:0x1001': 'Bixolon SRP-350 Receipt Printer',
        '0x1606:0x1002': 'Bixolon SRP-350II Receipt Printer',
        '0x1606:0x1005': 'Bixolon SRP-275 Receipt Printer',
        '0x1606:0x2001': 'Bixolon SLP-D220 Label Printer',
        '0x1606:0x2005': 'Bixolon SLP-T400 Label Printer',
        
        // Zebra Label Printers
        '0x0a5f:0x000a': 'Zebra GK420d Label Printer',
        '0x0a5f:0x001a': 'Zebra GC420d Label Printer',
        '0x0a5f:0x002a': 'Zebra ZD410 Label Printer',
        '0x0a5f:0x003a': 'Zebra ZD620 Label Printer',
        '0x0a5f:0x005a': 'Zebra ZT230 Label Printer',
        '0x0a5f:0x007a': 'Zebra ZT410 Label Printer',
        
        // Citizen
        '0x0483:0x0701': 'Citizen CT-E351 Receipt Printer',
        '0x0483:0x0702': 'Citizen CT-S310 Receipt Printer',
        
        // POS-X
        '0x045e:0x00bb': 'POS-X Thermal Printer',
        '0x045e:0x00bc': 'POS-X Receipt Printer',

        // Datecs
        '0x060b:0x1001': 'Datecs DP-50 Receipt Printer',
        '0x060b:0x1002': 'Datecs FP-600 POS Printer',
        
        // Custom / Self-identified
        '0x0000:0x0000': 'Unknown USB Device',
    };

    function getDeviceDisplayName(device) {
        // Strategy 1: Use known device database (best match)
        const vid = Number(device.vendorId).toString(16).toUpperCase().padStart(4, '0');
        const pid = Number(device.productId).toString(16).toUpperCase().padStart(4, '0');
        const key = '0x' + vid + ':0x' + pid;
        const knownName = KNOWN_USB_DEVICES[key];
        if (knownName) return knownName;

        // Strategy 2: Use productName if available
        if (device.productName && device.productName !== 'Unknown' && device.productName.trim()) {
            if (device.manufacturerName && device.manufacturerName !== 'Unknown' && device.manufacturerName.trim()) {
                return device.manufacturerName + ' ' + device.productName;
            }
            return device.productName;
        }

        // Strategy 3: Use manufacturerName only
        if (device.manufacturerName && device.manufacturerName !== 'Unknown' && device.manufacturerName.trim()) {
            return device.manufacturerName + ' (0x' + vid + ':0x' + pid + ')';
        }

        // Strategy 4: Check common manufacturer by VID prefix
        const knownManufacturers = {
            '04b8': 'Epson',
            '0519': 'Star Micronics',
            '0416': 'Chinese Thermal Printer',
            '067b': 'Prolink/POS Printer',
            '1a86': 'QinHeng (CH340)',
            '10c4': 'Silicon Labs (CP210x)',
            '0403': 'FTDI',
            '0c2e': 'Honeywell',
            '05f9': 'Zebra/Symbol',
            '0681': 'Wasp',
            '0581': 'Fujitsu',
            '0764': 'CipherLab',
            '1fc9': 'Xprinter',
            '1606': 'Bixolon',
            '0a5f': 'Zebra',
            '0483': 'STM/Citizen',
            '060b': 'Datecs',
            '045e': 'Microsoft/POS-X',
            '1915': 'Newland',
            '11f5': 'Newland',
        };
        const manufacturer = knownManufacturers[vid] || '';
        
        // Strategy 5: Just show the VID:PID in a readable format
        if (manufacturer) {
            return manufacturer + ' (0x' + vid + ':0x' + pid + ')';
        }
        
        return 'Perangkat USB (0x' + vid + ':0x' + pid + ')';
    }

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
