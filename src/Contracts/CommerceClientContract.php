<?php

namespace StellarSecurity\CommerceLaravel\Contracts;

use StellarSecurity\CommerceLaravel\DTO\CreateOrderRequest;
use StellarSecurity\CommerceLaravel\DTO\UpdateStatusRequest;

interface CommerceClientContract
{
    public function listProducts(array $query = [], ?string $requestId = null): array;

    public function getProduct(string $slug, array $query = [], ?string $requestId = null): array;

    public function createOrder(CreateOrderRequest $request, ?string $requestId = null): array;

    public function getOrder(string $orderId, ?string $requestId = null): array;

    public function updateOrderStatus(string $orderId, UpdateStatusRequest $request, ?string $requestId = null): array;
}
