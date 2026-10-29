<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Model;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreScopeResolver
{
    public function __construct(
        private readonly StoreRepositoryInterface $storeRepository,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function resolve($storeId = null, $storeCode = null): StoreInterface
    {
        if ($storeCode !== null && trim((string)$storeCode) !== '') {
            return $this->storeRepository->get(trim((string)$storeCode));
        }

        $storeId = $storeId === null ? null : (int)$storeId;
        if ($storeId !== null && $storeId > 0) {
            return $this->storeRepository->getById($storeId);
        }

        $store = $this->storeManager->getStore();
        if ((int)$store->getId() > 0) {
            return $store;
        }

        $defaultStore = $this->storeManager->getDefaultStoreView();
        if ($defaultStore instanceof StoreInterface) {
            return $defaultStore;
        }

        return $this->storeRepository->getById(1);
    }

    public function scope(StoreInterface $store): array
    {
        $website = $store->getWebsite();

        return [
            'store_id' => (int)$store->getId(),
            'store_code' => (string)$store->getCode(),
            'website_id' => (int)$website->getId(),
            'website_code' => (string)$website->getCode(),
        ];
    }
}
