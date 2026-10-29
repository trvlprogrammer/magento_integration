<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Api\Data;

interface ShippingMethodInterface
{
    public const CODE = 'code';
    public const CARRIER_CODE = 'carrier_code';
    public const CARRIER_TITLE = 'carrier_title';
    public const METHOD_CODE = 'method_code';
    public const NAME = 'name';
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
    public function getCarrierCode(): string;

    /**
     * @return string
     */
    public function getCarrierTitle(): string;

    /**
     * @return string
     */
    public function getMethodCode(): string;

    /**
     * @return string
     */
    public function getName(): string;

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
