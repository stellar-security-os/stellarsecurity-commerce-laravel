<?php

namespace StellarSecurity\CommerceLaravel\DTO;

class CheckCouponCodeRequest
{
    public function __construct(
        public string $couponCode
    ) {}

    public function toArray(): array
    {
        return [
            'coupon_code' => $this->couponCode,
        ];
    }
}
