# Trvlprogrammer Integration

Magento Open Source 2.4.7 module that exposes authenticated, read-only REST endpoints for
Odoo eCommerce delivery and payment method synchronization.

## Requirements

- Magento Open Source 2.4.7
- PHP 8.1, 8.2, or 8.3
- An integration access token with the `eCommerce Method Synchronization`
  (`Trvlprogrammer_Integration::methods`) resource

## Installation

1. Copy this repository to `app/code/Trvlprogrammer/Integration`.
2. Enable and install the module:

   ```bash
   bin/magento module:enable Trvlprogrammer_Integration
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento cache:flush
   ```

3. In **System > Integrations**, edit the integration used by Odoo and grant
   **eCommerce Method Synchronization** under **Available APIs**. Save the integration and
   reactivate it if Magento regenerates its access token.
4. Use the integration access token as a bearer token:

   ```bash
   export MAGENTO_BASE_URL=http://127.0.0.1:8083
   export MAGENTO_TOKEN='<integration-access-token>'
   export MAGENTO_STORE_CODE=default
   export MAGENTO_STORE_ID=1
   tests/api-smoke.sh
   ```

## API contracts

Both endpoints require `Authorization: Bearer <integration-token>` and return a JSON array.
`storeId` and `storeCode` are optional query parameters. When both are omitted the current
request store is used; for an admin/integration request the default store view is used.
An explicit `storeId` or `storeCode` is recommended.

### `GET /rest/{store_code}/V1/odoo-integration/shipping-methods`

```json
[
  {
    "code": "flatrate_flatrate",
    "carrier_code": "flatrate",
    "carrier_title": "Flat Rate",
    "method_code": "flatrate",
    "name": "Fixed",
    "is_active": true,
    "store_id": 1,
    "store_code": "default",
    "website_id": 1,
    "website_code": "base"
  }
]
```

- `code` is the stable Magento shipping method code (`carrier_method`) used in orders.
- All configured carriers are returned, including inactive carriers (`is_active: false`).
- Methods are read from Magento shipping carrier models; no method names or credentials are
  hardcoded. Carrier or method read failures are logged without exposing configuration values.
- Only stable method metadata is returned. No API keys, passwords, private keys, or raw
  carrier configuration are exposed.

### `GET /rest/{store_code}/V1/odoo-integration/payment-methods`

```json
[
  {
    "code": "checkmo",
    "title": "Check / Money order",
    "is_active": true,
    "store_id": 1,
    "store_code": "default",
    "website_id": 1,
    "website_code": "base"
  }
]
```

Payment methods come from `Magento\Payment\Api\PaymentMethodListInterface::getList()`, so
inactive methods are included and methods are resolved through Magento service contracts.

## Security model

- Routes are protected by the `Trvlprogrammer_Integration::methods` ACL resource.
- Shipping data is obtained through `Magento\Shipping\Model\Config` and carrier service
  implementations.
- Payment data is obtained through `Magento\Payment\Api\PaymentMethodListInterface`.
- The module performs no direct database queries and exposes no configuration values.
- Dependencies are injected through `di.xml`; both public APIs are service contracts with
  data interfaces in `Api/Data`.

## Tests

Unit tests:

```bash
docker exec magento-e2e-php sh -lc \
  'MAGENTO_ROOT=/var/www/html MODULE_DIR=/var/www/html/app/code/Trvlprogrammer/Integration \
   /var/www/html/app/code/Trvlprogrammer/Integration/tests/run-unit-tests.sh'
```

The runner uses Magento's vendored PHPUnit when available and otherwise downloads a pinned
PHPUnit 9 phar to `/tmp`. Run `setup:di:compile` first so generated factories exist.

Authenticated integration smoke test:

```bash
MAGENTO_BASE_URL=http://127.0.0.1:8083 \
MAGENTO_TOKEN='<integration-access-token>' \
MAGENTO_STORE_CODE=default \
MAGENTO_STORE_ID=1 \
tests/api-smoke.sh
```
