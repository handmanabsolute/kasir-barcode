<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $cartItems
     */
    public function checkout(User $user, array $cartItems, int $paidAmount): Sale
    {
        if ($cartItems === []) {
            throw ValidationException::withMessages([
                'cart' => ['Keranjang masih kosong.'],
            ]);
        }

        return DB::transaction(function () use ($user, $cartItems, $paidAmount): Sale {
            $subtotal = 0;
            $totalCost = 0;
            $preparedItems = [];

            foreach ($cartItems as $item) {
                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        'cart' => ['Produk tidak ditemukan atau tidak aktif.'],
                    ]);
                }

                $quantity = max(1, (int) $item['quantity']);

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => ["Stok {$product->name} tidak mencukupi (tersisa {$product->stock})."],
                    ]);
                }

                $lineTotal = $product->sell_price * $quantity;
                $lineCost = $product->cost_price * $quantity;
                $lineProfit = $lineTotal - $lineCost;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                    'line_cost' => $lineCost,
                    'line_profit' => $lineProfit,
                ];

                $subtotal += $lineTotal;
                $totalCost += $lineCost;
            }

            if ($paidAmount < $subtotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => ['Nominal bayar kurang dari total belanja.'],
                ]);
            }

            $sale = Sale::query()->create([
                'user_id' => $user->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'total_cost' => $totalCost,
                'profit' => $subtotal - $totalCost,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $subtotal,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            foreach ($preparedItems as $item) {
                /** @var Product $product */
                $product = $item['product'];

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'barcode' => $product->barcode,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $product->cost_price,
                    'unit_price' => $product->sell_price,
                    'line_total' => $item['line_total'],
                    'line_cost' => $item['line_cost'],
                    'line_profit' => $item['line_profit'],
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            return $sale->fresh(['items']);
        });
    }

    protected function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Sale::query()->where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}
