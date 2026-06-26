<?php

namespace App\Support;

class Money
{
    public static function format(int|float $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
