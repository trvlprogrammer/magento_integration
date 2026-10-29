<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Model;

use Magento\Shipping\Model\Config as ShippingConfig;
use Psr\Log\LoggerInterface;
use Trvlprogrammer\Integration\Api\Data\ShippingMethodInterface;
use Trvlprogrammer\Integration\Api\ShippingMethodManagementInterface;

class ShippingMethodManagement implements ShippingMethodManagementInterface
{
    public function __construct(
        private readonly StoreScopeResolver $storeScopeResolver,
        private readonly ShippingConfig $shippingConfig,
        private readonly ShippingMethodFactory $shippingMethodFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getList($storeId = null, $storeCode = null)
    {
        $store = $this->storeScopeResolver->resolve($storeId, $storeCode);
        $scope = $this->storeScopeResolver->scope($store);
        $activeCarriers = $this->shippingConfig->getActiveCarriers($store);
        $allCarriers = $this->shippingConfig->getAllCarriers($store);
        $methods = [];

        foreach ($allCarriers as $carrierCode => $carrier) {
            $carrierCode = (string)$carrierCode;
            $carrierTitle = $this->carrierTitle($carrier, $carrierCode);
            $isActive = isset($activeCarriers[$carrierCode]);

            try {
                $allowedMethods = $carrier->getAllowedMethods();
            } catch (\Throwable $exception) {
                $this->logger->warning(
                    sprintf(
                        'Unable to read shipping methods for carrier %s: %s',
                        $carrierCode,
                        get_class($exception)
                    )
                );
                $allowedMethods = [];
            }

            if (!is_array($allowedMethods) || $allowedMethods === []) {
                $allowedMethods = [$carrierCode => $carrierTitle];
            }

            foreach ($allowedMethods as $methodCode => $methodTitle) {
                $methodCode = (string)$methodCode;
                $methodCode = $methodCode === '' ? $carrierCode : $methodCode;
                $methodTitle = trim((string)$methodTitle);
                $methods[] = $this->shippingMethodFactory->create(
                    [
                        'data' => [
                            ShippingMethodInterface::CODE => $carrierCode . '_' . $methodCode,
                            ShippingMethodInterface::CARRIER_CODE => $carrierCode,
                            ShippingMethodInterface::CARRIER_TITLE => $carrierTitle,
                            ShippingMethodInterface::METHOD_CODE => $methodCode,
                            ShippingMethodInterface::NAME => $methodTitle !== ''
                                ? $methodTitle
                                : $carrierTitle,
                            ShippingMethodInterface::IS_ACTIVE => $isActive,
                            ShippingMethodInterface::STORE_ID => $scope['store_id'],
                            ShippingMethodInterface::STORE_CODE => $scope['store_code'],
                            ShippingMethodInterface::WEBSITE_ID => $scope['website_id'],
                            ShippingMethodInterface::WEBSITE_CODE => $scope['website_code'],
                        ],
                    ]
                );
            }
        }

        usort(
            $methods,
            static fn (ShippingMethodInterface $left, ShippingMethodInterface $right): int =>
                [$left->getCarrierCode(), $left->getMethodCode()] <=>
                [$right->getCarrierCode(), $right->getMethodCode()]
        );

        return $methods;
    }

    private function carrierTitle($carrier, string $carrierCode): string
    {
        $title = '';
        try {
            $title = trim((string)$carrier->getConfigData('title'));
            if ($title === '') {
                $title = trim((string)$carrier->getConfigData('name'));
            }
        } catch (\Throwable $exception) {
            $this->logger->warning(
                sprintf(
                    'Unable to read shipping carrier title %s: %s',
                    $carrierCode,
                    get_class($exception)
                )
            );
        }

        return $title !== '' ? $title : $carrierCode;
    }
}
