<?php

namespace StellarSecurity\CommerceLaravel\DTO;

class UpdateStatusRequest
{
    public function __construct(
        public string $status,
        public ?string $reason = null,
        public ?array $meta = null
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'reason' => $this->reason,
            'meta' => $this->meta,
        ];
    }
}
