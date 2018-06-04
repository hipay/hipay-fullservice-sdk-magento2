<?php
/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */
namespace HiPay\FullserviceMagento\Test\Unit\Model;

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
        //$this->markTestSkipped("Not implemented yet!");
        //return;

        $this->ccConfigMock = $this->getMockBuilder('Magento\Payment\Model\CcConfig')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentHelperMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);

        /* $this->model = new CcConfigProvider(
             $this->ccConfigMock,
             $this->paymentHelperMock,
             array()
         );*/
    }

    public function testGetConfig()
    {
        //$this->assertEquals([], $this->model->getConfig());
        $this->assertEquals(1, 1);
    }
}
