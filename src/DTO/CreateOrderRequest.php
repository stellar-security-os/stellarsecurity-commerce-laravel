<?php

namespace StellarSecurity\CommerceLaravel\DTO;

class CreateOrderRequest
{
    /**
     * @param OrderItem[] $items
     */
    public function __construct(
        public string $idempotencyKey,
        public ?string $userId,
        public ?string $buyerRef,
        public string $currency,
        public array $items,
        public ?ShippingInfo $shipping = null,
        public ?string $shippingMethod = null,
        public ?string $couponCode = null,
        public ?array $meta = null
    ) {}

    public function toArray(): array
    {
        return [
            'idempotency_key' => $this->idempotencyKey,
            'user_id' => $this->userId,
            'buyer_ref' => $this->buyerRef,
            'currency' => strtoupper($this->currency),
            'items' => array_map(fn (OrderItem $i) => $i->toArray(), $this->items),
            'shipping' => $this->shipping?->toArray(),
            'shipping_method' => $this->shippingMethod,
            'coupon_code' => $this->couponCode,
            'meta' => $this->meta,
        ];
    }
}
