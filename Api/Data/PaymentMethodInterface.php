<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Api\Data;

interface PaymentMethodInterface
{
    public const CODE = 'code';
    public const TITLE = 'title';
    public const IS_ACTIVE = 'is_active';
    public const STORE_ID = 'store_id';
    public const STORE_CODE = 'store_code';
    public const WEBSITE_ID = 'website_id';
    public const WEBSITE_CODE = 'website_code';

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @return string
     */
    public function getStoreCode(): string;

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @return string
     */
    public function getWebsiteCode(): string;
}
