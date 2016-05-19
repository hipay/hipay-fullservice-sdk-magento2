<?php

namespace HiPay\Fullservice\Test\Unit\Model;

use Magento\Payment\Helper\Data;
use HiPay\FullserviceMagento\Model\Config as CcConfig;
use HiPay\FullserviceMagento\Model\CcConfigProvider;

class CcConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var CcConfigProvider */
    protected $model;

    /** @var CcConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $ccConfigMock;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentHelperMock;

    protected function setUp()
    {
    	$this->markTestSkipped("Not implemented yet!");
    	return;
    	
    	$this->ccConfigMock = $this->getMockBuilder('Magento\Payment\Model\CcConfig')
    	->disableOriginalConstructor()
    	->getMock();

        $this->paymentHelperMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);

        $this->model = new CcConfigProvider(
            $this->ccConfigMock,
            $this->paymentHelperMock,
        	array()
        );
    }

    public function testGetConfig()
    {
        $this->assertEquals([], $this->model->getConfig());
    }
}
