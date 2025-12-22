<?php

namespace StellarSecurity\CommerceLaravel\Exceptions;

use RuntimeException;

class CommerceClientException extends RuntimeException
{
    public function __construct(
        string $message,
        public ?int $statusCode = null,
        public ?array $responseBody = null
    ) {
        parent::__construct($message);
    }
}
