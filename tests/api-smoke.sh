#!/usr/bin/env bash
set -euo pipefail

: "${MAGENTO_BASE_URL:?Set MAGENTO_BASE_URL, for example http://127.0.0.1:8083}"
: "${MAGENTO_TOKEN:?Set MAGENTO_TOKEN to an integration access token}"
MAGENTO_STORE_CODE="${MAGENTO_STORE_CODE:-default}"
MAGENTO_STORE_ID="${MAGENTO_STORE_ID:-}"

python3 - "$MAGENTO_BASE_URL" "$MAGENTO_TOKEN" "$MAGENTO_STORE_CODE" "$MAGENTO_STORE_ID" <<'PY'
import json
import sys
import urllib.parse
import urllib.request

base, token, store_code, store_id = sys.argv[1:5]
if not store_id:
    store_id = None
headers = {"Authorization": f"Bearer {token}", "Accept": "application/json"}

def get(path, extra=None):
    params = {}
    if store_id:
        params["storeId"] = store_id
    if store_code:
        params["storeCode"] = store_code
    params.update(extra or {})
    url = f"{base.rstrip('/')}/rest/{store_code}/V1/{path}?{urllib.parse.urlencode(params)}"
    request = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(request, timeout=30) as response:
        assert response.status == 200, response.status
        return json.loads(response.read().decode())

shipping = get("odoo-integration/shipping-methods")
payment = get("odoo-integration/payment-methods")
assert isinstance(shipping, list) and shipping, "shipping method list is empty"
assert isinstance(payment, list) and payment, "payment method list is empty"
for method in shipping:
    for key in ("code", "carrier_code", "carrier_title", "method_code", "name", "is_active",
                "store_id", "store_code", "website_id", "website_code"):
        assert key in method, f"missing shipping key: {key}"
for method in payment:
    for key in ("code", "title", "is_active", "store_id", "store_code", "website_id", "website_code"):
        assert key in method, f"missing payment key: {key}"
serialized = json.dumps(shipping + payment).lower()
for forbidden in ("password", "secret", "token", "api_key", "private_key"):
    assert forbidden not in serialized, f"sensitive key leaked: {forbidden}"
print(json.dumps({
    "shipping_methods": len(shipping),
    "active_shipping_methods": sum(1 for item in shipping if item["is_active"]),
    "payment_methods": len(payment),
    "active_payment_methods": sum(1 for item in payment if item["is_active"]),
    "scope": {
        "store_id": shipping[0]["store_id"],
        "store_code": shipping[0]["store_code"],
        "website_id": shipping[0]["website_id"],
        "website_code": shipping[0]["website_code"],
    },
}, indent=2))
PY
