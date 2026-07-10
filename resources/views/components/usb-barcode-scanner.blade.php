@props(['onScan' => null])

<div 
    x-data="usbBarcodeScanner()"
    x-init="init()"
    class="hidden"
>
    <!-- Hidden component that listens for USB barcode scanner input -->
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('usbBarcodeScanner', () => ({
            scanBuffer: '',
            scanTimeout: null,
            lastKeyTime: 0,
            isScanning: false,
            connectedScanner: null,
            
            init() {
                this.detectUSBScanner();
                this.setupKeyboardListener();
                
                // Re-detect scanner when USB devices change
                if ('usb' in navigator) {
                    navigator.usb.addEventListener('connect', () => {
                        setTimeout(() => this.detectUSBScanner(), 500);
                    });
                    navigator.usb.addEventListener('disconnect', () => {
                        this.detectUSBScanner();
                    });
                }
            },
            
            async detectUSBScanner() {
                if (!('usb' in navigator)) {
                    console.log('WebUSB tidak didukung, menggunakan keyboard input fallback');
                    return;
                }
                
                try {
                    const devices = await navigator.usb.getDevices();
                    
                    // Look for barcode scanner devices
                    const scanners = devices.filter(d => {
                        const vid = d.vendorId;
                        // Common barcode scanner vendor IDs
                        return vid === 0x0c2e || // Honeywell
                               vid === 0x05f9 || // Zebra/Symbol
                               vid === 0x0681 || // Wasp
                               vid === 0x0581 || // Fujitsu
                               vid === 0x0764 || // CipherLab
                               vid === 0x11f5 || // Newland
                               vid === 0x1915;   // Newland
                    });
                    
                    if (scanners.length > 0) {
                        this.connectedScanner = scanners[0];
                        const vid = Number(this.connectedScanner.vendorId).toString(16).toUpperCase().padStart(4, '0');
                        const pid = Number(this.connectedScanner.productId).toString(16).toUpperCase().padStart(4, '0');
                        console.log(`✓ Barcode scanner terdeteksi: 0x${vid}:0x${pid}`);
                        
                        // Store scanner info
                        localStorage.setItem('usb_barcode_scanner_vendor_id', this.connectedScanner.vendorId);
                        localStorage.setItem('usb_barcode_scanner_product_id', this.connectedScanner.productId);
                    } else {
                        console.log('Tidak ada barcode scanner USB terdeteksi, menggunakan keyboard input');
                    }
                } catch (err) {
                    console.log('Gagal mendeteksi USB scanner:', err.message);
                }
            },
            
            setupKeyboardListener() {
                document.addEventListener('keypress', (e) => {
                    const now = Date.now();
                    
                    // Barcode scanners type very fast (< 50ms between characters)
                    // If time between keystrokes is less than 50ms, it's likely a scanner
                    if (now - this.lastKeyTime < 50) {
                        this.isScanning = true;
                    }
                    
                    this.lastKeyTime = now;
                    
                    // Clear timeout if exists
                    if (this.scanTimeout) {
                        clearTimeout(this.scanTimeout);
                    }
                    
                    // Enter key signals end of scan
                    if (e.key === 'Enter') {
                        if (this.scanBuffer.length > 0 && this.isScanning) {
                            this.processScan(this.scanBuffer.trim());
                            this.scanBuffer = '';
                            this.isScanning = false;
                        }
                        return;
                    }
                    
                    // Add character to buffer if it's a printable character
                    if (e.key.length === 1) {
                        this.scanBuffer += e.key;
                        
                        // Set timeout to reset buffer if scanning stops
                        this.scanTimeout = setTimeout(() => {
                            if (this.scanBuffer.length > 0 && this.isScanning) {
                                this.processScan(this.scanBuffer.trim());
                            }
                            this.scanBuffer = '';
                            this.isScanning = false;
                        }, 100);
                    }
                });
            },
            
            processScan(barcode) {
                if (barcode.length < 3) return; // Ignore very short scans
                
                console.log('Barcode scanned:', barcode);
                
                // Dispatch custom event with barcode data
                window.dispatchEvent(new CustomEvent('barcode-scanned', {
                    detail: barcode
                }));
                
                // Show toast notification
                if (window.Livewire) {
                    window.Livewire.dispatch('toast', {
                        text: `Barcode terdeteksi: ${barcode}`,
                        variant: 'success'
                    });
                }
            }
        }));
    });
</script>
