# Changelog

## 1.1.0
- Add coupon code validation endpoint client method: `checkCouponCode()` (+ DTO)

## 1.0.0
- Initial release: Products, Orders (create/get), Order status update
- Strict trust boundary: client does not send shipping/tax/discount cents
- Basic Auth support

## Unreleased

- Increased default Commerce Core HTTP timeout and connect timeout.
- Added internal fallback defaults for timeout/retry settings, so retry still works when retry env variables are missing or an older published config does not contain the retry block.
- Added configurable retry backoff for transient Commerce Core failures.
- Retries now cover connection exceptions and HTTP 408, 429, 500, 502, 503, and 504 responses.

