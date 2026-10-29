#!/usr/bin/env bash
set -euo pipefail

MAGENTO_ROOT="${MAGENTO_ROOT:-/var/www/html}"
MODULE_DIR="${MODULE_DIR:-$MAGENTO_ROOT/app/code/Trvlprogrammer/Integration}"
PHPUNIT="${PHPUNIT:-$MAGENTO_ROOT/vendor/bin/phpunit}"

if [[ ! -x "$PHPUNIT" ]]; then
    PHPUNIT="/tmp/phpunit-9.phar"
    if [[ ! -f "$PHPUNIT" ]]; then
        curl -fsSL https://phar.phpunit.de/phpunit-9.phar -o "$PHPUNIT"
    fi
fi

cd "$MAGENTO_ROOT"
php "$PHPUNIT" --no-configuration \
    --bootstrap "$MAGENTO_ROOT/dev/tests/unit/framework/bootstrap.php" \
    "$MODULE_DIR/Test/Unit"
