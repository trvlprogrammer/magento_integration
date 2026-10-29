<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface as MagentoPaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Trvlprogrammer\Integration\Api\Data\PaymentMethodInterface;
use Trvlprogrammer\Integration\Api\PaymentMethodManagementInterface;

class PaymentMethodManagement implements PaymentMethodManagementInterface
{
    public function __construct(
        private readonly StoreScopeResolver $storeScopeResolver,
        private readonly PaymentMethodListInterface $paymentMethodList,
        private readonly PaymentMethodFactory $paymentMethodFactory
    ) {
    }

    public function getList($storeId = null, $storeCode = null)
    {
        $store = $this->storeScopeResolver->resolve($storeId, $storeCode);
        $scope = $this->storeScopeResolver->scope($store);
        $methods = [];

        foreach ($this->paymentMethodList->getList((int)$store->getId()) as $method) {
            if (!$method instanceof MagentoPaymentMethodInterface) {
                continue;
            }
            $code = trim((string)$method->getCode());
            if ($code === '') {
                continue;
            }
            $title = trim((string)$method->getTitle());
            $methods[] = $this->paymentMethodFactory->create(
                [
                    'data' => [
                        PaymentMethodInterface::CODE => $code,
                        PaymentMethodInterface::TITLE => $title !== '' ? $title : $code,
                        PaymentMethodInterface::IS_ACTIVE => (bool)$method->getIsActive(),
                        PaymentMethodInterface::STORE_ID => $scope['store_id'],
                        PaymentMethodInterface::STORE_CODE => $scope['store_code'],
                        PaymentMethodInterface::WEBSITE_ID => $scope['website_id'],
                        PaymentMethodInterface::WEBSITE_CODE => $scope['website_code'],
                    ],
                ]
            );
        }

        usort(
            $methods,
            static fn (PaymentMethodInterface $left, PaymentMethodInterface $right): int =>
                $left->getCode() <=> $right->getCode()
        );

        return $methods;
    }
}
