<?php
declare(strict_types=1);

namespace Trvlprogrammer\Integration\Test\Unit\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface as MagentoPaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;
use Trvlprogrammer\Integration\Model\PaymentMethod;
use Trvlprogrammer\Integration\Model\PaymentMethodFactory;
use Trvlprogrammer\Integration\Model\PaymentMethodManagement;
use Trvlprogrammer\Integration\Model\StoreScopeResolver;

class PaymentMethodManagementTest extends TestCase
{
    public function testGetListIncludesInactiveMethodsWithScope(): void
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

        $active = $this->method('checkmo', 'Check / Money order', true);
        $inactive = $this->method('banktransfer', 'Bank Transfer Payment', false);
        $paymentList = $this->createMock(PaymentMethodListInterface::class);
        $paymentList->method('getList')->with(3)->willReturn([$active, $inactive]);

        $factory = $this->getMockBuilder(PaymentMethodFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $factory->method('create')->willReturnCallback(
            static fn (array $arguments): PaymentMethod =>
                new PaymentMethod($arguments['data'])
        );

        $management = new PaymentMethodManagement($resolver, $paymentList, $factory);
        $methods = $management->getList(3);

        $this->assertCount(2, $methods);
        $this->assertSame('banktransfer', $methods[0]->getCode());
        $this->assertFalse($methods[0]->getIsActive());
        $this->assertSame('checkmo', $methods[1]->getCode());
        $this->assertTrue($methods[1]->getIsActive());
        $this->assertSame('Check / Money order', $methods[1]->getTitle());
        $this->assertSame(3, $methods[1]->getStoreId());
        $this->assertSame('default', $methods[1]->getStoreCode());
        $this->assertSame(1, $methods[1]->getWebsiteId());
        $this->assertSame('base', $methods[1]->getWebsiteCode());
    }

    private function method(string $code, string $title, bool $active): MagentoPaymentMethodInterface
    {
        $method = $this->createMock(MagentoPaymentMethodInterface::class);
        $method->method('getCode')->willReturn($code);
        $method->method('getTitle')->willReturn($title);
        $method->method('getIsActive')->willReturn($active);
        return $method;
    }
}
