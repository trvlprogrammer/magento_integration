<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Api;

interface ShippingMethodManagementInterface
{
    /**
     * Return all configured shipping methods for a store scope.
     *
     * @param int|null $storeId
     * @param string|null $storeCode
     * @return \Trvlprogrammer\Integration\Api\Data\ShippingMethodInterface[]
     */
    public function getList($storeId = null, $storeCode = null);
}
