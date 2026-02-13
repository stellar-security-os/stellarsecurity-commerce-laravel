# stellarsecurity/stellarsecurity-commerce-laravel

Laravel client package used by UI APIs to call the Stellar Commerce Core/Base API.

## Install

```bash
composer require stellarsecurity/stellarsecurity-commerce-laravel
```

## Publish config

```bash
php artisan vendor:publish --tag=stellarsecurity-commerce-laravel-config
```

## Env

```env
STELLAR_COMMERCE_CORE_BASE_URL=http://127.0.0.1:8000
STELLAR_COMMERCE_CORE_API_PREFIX=/api/v1

STELLAR_COMMERCE_CORE_BASIC_AUTH_ENABLED=true
STELLAR_COMMERCE_CORE_BASIC_AUTH_USER=stellar
STELLAR_COMMERCE_CORE_BASIC_AUTH_PASS=CHANGE_THIS

STELLAR_COMMERCE_CORE_TIMEOUT_SECONDS=10
STELLAR_COMMERCE_CORE_CONNECT_TIMEOUT_SECONDS=5
STELLAR_COMMERCE_CORE_RETRY_TIMES=2
STELLAR_COMMERCE_CORE_RETRY_SLEEP_MS=200
```

## Important trust boundary

The UI API must not send money amounts like shipping/tax/discount.
Those must be computed by the Commerce Core API.

Send:
- items
- shipping address (if physical)
- shipping_method (optional)
- coupon_code (optional)

## Usage

```php
use StellarSecurity\CommerceLaravel\Contracts\CommerceClientContract;
use StellarSecurity\CommerceLaravel\DTO\CheckCouponCodeRequest;
use StellarSecurity\CommerceLaravel\DTO\CreateOrderRequest;
use StellarSecurity\CommerceLaravel\DTO\OrderItem;

public function __construct(private CommerceClientContract $commerce) {}

$req = new CreateOrderRequest(
  idempotencyKey: (string) \Illuminate\Support\Str::uuid(),
  userId: null,
  buyerRef: (string) \Illuminate\Support\Str::uuid(),
  currency: 'EUR',
  items: [ new OrderItem($variantId, 1) ],
  shipping: null,
  shippingMethod: null,
  couponCode: null,
  meta: ['channel' => 'direct']
);

$result = $this->commerce->createOrder($req);

// Check a coupon code (exists + discount info)
$couponResult = $this->commerce->checkCouponCode(
  new CheckCouponCodeRequest(couponCode: 'TEST10')
);
```
