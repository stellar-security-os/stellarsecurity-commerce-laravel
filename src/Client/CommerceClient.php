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
    private const DEFAULT_TIMEOUT_SECONDS = 30;
    private const DEFAULT_CONNECT_TIMEOUT_SECONDS = 10;
    private const DEFAULT_RETRY_TIMES = 5;
    private const DEFAULT_RETRY_SLEEP_MS = 1000;
    private const DEFAULT_RETRY_MULTIPLIER = 2;
    private const DEFAULT_RETRY_MAX_SLEEP_MS = 10000;

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
        $retryConfig = (array) ($this->config['retry'] ?? []);

        $timeoutSeconds = max(1, (int) ($this->config['timeout_seconds'] ?? self::DEFAULT_TIMEOUT_SECONDS));
        $connectTimeoutSeconds = max(1, (int) ($this->config['connect_timeout_seconds'] ?? self::DEFAULT_CONNECT_TIMEOUT_SECONDS));
        $retryTimes = max(0, (int) ($retryConfig['times'] ?? self::DEFAULT_RETRY_TIMES));
        $retrySleepMs = max(0, (int) ($retryConfig['sleep_ms'] ?? self::DEFAULT_RETRY_SLEEP_MS));
        $retryMultiplier = max(1, (int) ($retryConfig['multiplier'] ?? self::DEFAULT_RETRY_MULTIPLIER));
        $retryMaxSleepMs = max($retrySleepMs, (int) ($retryConfig['max_sleep_ms'] ?? self::DEFAULT_RETRY_MAX_SLEEP_MS));

        $pending = Http::timeout($timeoutSeconds)
            ->connectTimeout($connectTimeoutSeconds)
            ->acceptJson()
            ->withHeaders((array) ($this->config['headers'] ?? []));

        $basic = (array) ($this->config['basic_auth'] ?? []);
        if (($basic['enabled'] ?? false) === true) {
            $pending = $pending->withBasicAuth((string) ($basic['username'] ?? ''), (string) ($basic['password'] ?? ''));
        }

        if ($retryTimes > 0) {
            $pending = $pending->retry(
                $retryTimes,
                function (int $attempt) use ($retrySleepMs, $retryMultiplier, $retryMaxSleepMs): int {
                    if ($retrySleepMs <= 0) {
                        return 0;
                    }

                    $sleep = $retrySleepMs * ($retryMultiplier ** max(0, $attempt - 1));

                    return (int) min($sleep, $retryMaxSleepMs);
                },
                function ($exception): bool {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    if ($exception instanceof RequestException) {
                        $code = $exception->response?->status();

                        return is_int($code) && in_array($code, [408, 429, 500, 502, 503, 504], true);
                    }

                    return false;
                },
                true
            );
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
