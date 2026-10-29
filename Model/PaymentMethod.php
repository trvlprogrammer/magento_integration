<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Model;

use Magento\Framework\DataObject;
use Trvlprogrammer\Integration\Api\Data\PaymentMethodInterface;

class PaymentMethod extends DataObject implements PaymentMethodInterface
{
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(self::TITLE);
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
