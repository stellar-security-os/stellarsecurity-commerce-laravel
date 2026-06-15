<?php

namespace StellarSecurity\CommerceLaravel\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use StellarSecurity\CommerceLaravel\Contracts\CommerceClientContract;
use StellarSecurity\CommerceLaravel\DTO\CheckCouponCodeRequest;
use StellarSecurity\CommerceLaravel\DTO\CreateOrderRequest;
use StellarSecurity\CommerceLaravel\DTO\UpdateStatusRequest;
use StellarSecurity\CommerceLaravel\Exceptions\NotFoundException;
use StellarSecurity\CommerceLaravel\Exceptions\RateLimitedException;
use StellarSecurity\CommerceLaravel\Exceptions\UnauthorizedException;
use StellarSecurity\CommerceLaravel\Exceptions\UpstreamException;
use StellarSecurity\CommerceLaravel\Exceptions\ValidationException;

class CommerceClient implements CommerceClientContract
{
    public function __construct(private array $config) {}

    public function listProducts(array $query = [], ?string $requestId = null): array
    {
        return $this->get('/products', $query, $requestId);
    }

    public function getProduct(string $slug, array $query = [], ?string $requestId = null): array
    {
        return $this->get('/products/' . urlencode($slug), $query, $requestId);
    }

    public function createOrder(CreateOrderRequest $request, ?string $requestId = null): array
    {
        return $this->post('/orders', $request->toArray(), $requestId);
    }

    public function getOrder(string $orderId, ?string $requestId = null): array
    {
        return $this->get('/orders/' . urlencode($orderId), [], $requestId);
    }

    /**
     * NEW: Base API - list orders with filters + pagination
     */
    public function listOrders(array $query = [], ?string $requestId = null): array
    {
        return $this->get('/orders', $query, $requestId);
    }

    /**
     * NEW: Base API - list order items
     */
    public function listOrderItems(array $query = [], ?string $requestId = null): array
    {
        return $this->get('/order-items', $query, $requestId);
    }



    public function updateOrderStatus(string $orderId, UpdateStatusRequest $request, ?string $requestId = null): array
    {
        return $this->patch('/orders/' . urlencode($orderId) . '/status', $request->toArray(), $requestId);
    }

    public function checkCouponCode(CheckCouponCodeRequest $request, ?string $requestId = null): array
    {
        return $this->post('/coupons/check', $request->toArray(), $requestId);
    }

    private function get(string $path, array $query = [], ?string $requestId = null): array
    {
        $response = $this->request()
            ->withHeaders($this->makeHeaders($requestId))
            ->get($this->url($path), $query);

        return $this->handleResponse($response);
    }

    private function post(string $path, array $json = [], ?string $requestId = null): array
    {
        $response = $this->request()
            ->withHeaders($this->makeHeaders($requestId))
            ->post($this->url($path), $json);

        return $this->handleResponse($response);
    }

    private function patch(string $path, array $json = [], ?string $requestId = null): array
    {
        $response = $this->request()
            ->withHeaders($this->makeHeaders($requestId))
            ->patch($this->url($path), $json);

        return $this->handleResponse($response);
    }

    private function request(): PendingRequest
    {
        $retryTimes = (int) ($this->config['retry']['times'] ?? 0);
        $retrySleepMs = (int) ($this->config['retry']['sleep_ms'] ?? 0);

        $pending = Http::timeout((int) ($this->config['timeout_seconds'] ?? 10))
            ->connectTimeout((int) ($this->config['connect_timeout_seconds'] ?? 5))
            ->acceptJson()
            ->withHeaders((array) ($this->config['headers'] ?? []));

        $basic = (array) ($this->config['basic_auth'] ?? []);
        if (($basic['enabled'] ?? false) === true) {
            $pending = $pending->withBasicAuth((string) ($basic['username'] ?? ''), (string) ($basic['password'] ?? ''));
        }

        if ($retryTimes > 0) {
            $pending = $pending->retry($retryTimes, $retrySleepMs, function ($exception) {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                if ($exception instanceof RequestException) {
                    $code = $exception->response?->status();
                    return is_int($code) && $code >= 500;
                }

                return false;
            }, true);
        }

        return $pending;
    }

    private function url(string $path): string
    {
        $base = rtrim((string) ($this->config['base_url'] ?? ''), '/');
        $prefix = '/' . ltrim((string) ($this->config['api_prefix'] ?? '/api/v1'), '/');
        $path = '/' . ltrim($path, '/');

        return $base . $prefix . $path;
    }

    private function makeHeaders(?string $requestId): array
    {
        return [
            'X-Request-Id' => $requestId ?: (string) Str::uuid(),
        ];
    }

    private function handleResponse(Response $response): array
    {
        $status = $response->status();

        $body = $response->json();
        if (!is_array($body)) {
            $body = ['raw' => $response->body()];
        }

        if ($status >= 200 && $status < 300) {
            return $body;
        }

        if ($status === 401) {
            throw new UnauthorizedException('Unauthorized from Commerce Core API.', 401, $body);
        }

        if ($status === 404) {
            throw new NotFoundException('Resource not found in Commerce Core API.', 404, $body);
        }

        if ($status === 422) {
            throw new ValidationException('Validation failed in Commerce Core API.', 422, $body);
        }

        if ($status === 429) {
            throw new RateLimitedException('Rate limited by Commerce Core API.', 429, $body);
        }

        throw new UpstreamException('Commerce Core API error.', $status, $body);
    }
}
