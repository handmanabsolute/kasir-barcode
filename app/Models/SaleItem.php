<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'barcode',
        'quantity',
        'unit_cost',
        'unit_price',
        'line_total',
        'line_cost',
        'line_profit',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'integer',
            'unit_price' => 'integer',
            'line_total' => 'integer',
            'line_cost' => 'integer',
            'line_profit' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
