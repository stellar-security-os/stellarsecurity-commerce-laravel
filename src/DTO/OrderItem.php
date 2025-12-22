<?php

namespace StellarSecurity\CommerceLaravel\DTO;

class OrderItem
{
    public function __construct(
        public string $variantId,
        public int $qty = 1
    ) {}

    public function toArray(): array
    {
        return [
            'variant_id' => $this->variantId,
            'qty' => $this->qty,
        ];
    }
}
