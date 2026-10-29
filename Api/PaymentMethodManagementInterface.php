<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Api;

interface PaymentMethodManagementInterface
{
    /**
     * Return all configured payment methods for a store scope.
     *
     * @param int|null $storeId
     * @param string|null $storeCode
     * @return \Trvlprogrammer\Integration\Api\Data\PaymentMethodInterface[]
     */
    public function getList($storeId = null, $storeCode = null);
}
