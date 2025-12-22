<?php

namespace StellarSecurity\CommerceLaravel\DTO;

class ShippingInfo
{
    public function __construct(
        public string $fullName,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public ?string $state,
        public string $postalCode,
        public string $countryCode,
        public ?string $phone = null,
        public ?string $email = null,
        public ?array $meta = null,
    ) {}

    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'address_line1' => $this->addressLine1,
            'address_line2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country_code' => strtoupper($this->countryCode),
            'phone' => $this->phone,
            'email' => $this->email,
            'meta' => $this->meta,
        ];
    }
}
