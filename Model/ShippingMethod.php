<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Model;

use Magento\Framework\DataObject;
use Trvlprogrammer\Integration\Api\Data\ShippingMethodInterface;

class ShippingMethod extends DataObject implements ShippingMethodInterface
{
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    public function getCarrierCode(): string
    {
        return (string)$this->getData(self::CARRIER_CODE);
    }

    public function getCarrierTitle(): string
    {
        return (string)$this->getData(self::CARRIER_TITLE);
    }

    public function getMethodCode(): string
    {
        return (string)$this->getData(self::METHOD_CODE);
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    public function getStoreCode(): string
    {
        return (string)$this->getData(self::STORE_CODE);
    }

    public function getWebsiteId(): int
    {
        return (int)$this->getData(self::WEBSITE_ID);
    }

    public function getWebsiteCode(): string
    {
        return (string)$this->getData(self::WEBSITE_CODE);
    }
}
