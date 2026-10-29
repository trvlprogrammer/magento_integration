<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Trvlprogrammer\Integration\Api\Data\ShippingMethodInterface;
use Trvlprogrammer\Integration\Model\ShippingMethod;
use Trvlprogrammer\Integration\Model\ShippingMethodFactory;
use Trvlprogrammer\Integration\Model\ShippingMethodManagement;
use Trvlprogrammer\Integration\Model\StoreScopeResolver;

class ShippingMethodManagementTest extends TestCase
{
    public function testGetListIncludesInactiveCarrierWithScopeAndStableCode(): void
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getCode', 'getWebsite'])
            ->getMock();
        $store->method('getId')->willReturn(3);
        $store->method('getCode')->willReturn('default');
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getCode'])
            ->getMock();
        $website->method('getId')->willReturn(1);
        $website->method('getCode')->willReturn('base');
        $store->method('getWebsite')->willReturn($website);

        $resolver = $this->createMock(StoreScopeResolver::class);
        $resolver->method('resolve')->with(3, null)->willReturn($store);
        $resolver->method('scope')->with($store)->willReturn([
            'store_id' => 3,
            'store_code' => 'default',
            'website_id' => 1,
            'website_code' => 'base',
        ]);

        $activeCarrier = $this->carrier('flatrate', 'Flat Rate', ['flatrate' => 'Fixed']);
        $inactiveCarrier = $this->carrier('dhl', 'DHL', ['1' => 'Domestic express']);

        $shippingConfig = $this->createMock(ShippingConfig::class);
        $shippingConfig->method('getActiveCarriers')->with($store)
            ->willReturn(['flatrate' => $activeCarrier]);
        $shippingConfig->method('getAllCarriers')->with($store)
            ->willReturn(['flatrate' => $activeCarrier, 'dhl' => $inactiveCarrier]);

        $factory = $this->getMockBuilder(ShippingMethodFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $factory->method('create')->willReturnCallback(
            static fn (array $arguments): ShippingMethod =>
                new ShippingMethod($arguments['data'])
        );

        $management = new ShippingMethodManagement(
            $resolver,
            $shippingConfig,
            $factory,
            $this->createMock(LoggerInterface::class)
        );
        $methods = $management->getList(3);

        $this->assertCount(2, $methods);
        $this->assertSame('dhl_1', $methods[0]->getCode());
        $this->assertSame('dhl', $methods[0]->getCarrierCode());
        $this->assertSame('1', $methods[0]->getMethodCode());
        $this->assertFalse($methods[0]->getIsActive());
        $this->assertSame('flatrate_flatrate', $methods[1]->getCode());
        $this->assertTrue($methods[1]->getIsActive());
        $this->assertSame(3, $methods[1]->getStoreId());
        $this->assertSame('default', $methods[1]->getStoreCode());
        $this->assertSame(1, $methods[1]->getWebsiteId());
        $this->assertSame('base', $methods[1]->getWebsiteCode());
    }

    private function carrier(string $code, string $title, array $methods)
    {
        $carrier = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getAllowedMethods', 'getConfigData'])
            ->getMock();
        $carrier->method('getAllowedMethods')->willReturn($methods);
        $carrier->method('getConfigData')->willReturnCallback(
            static fn (string $field): string => $field === 'title' ? $title : ''
        );
        return $carrier;
    }
}
