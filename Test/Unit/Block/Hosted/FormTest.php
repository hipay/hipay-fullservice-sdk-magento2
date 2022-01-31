<?php

/**
 * HiPay fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */

namespace HiPay\FullserviceMagento\Test\Unit\Block\Hosted;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject $resolverMock
     */
    protected $resolverMock;

    /**
     * @var \HiPay\FullserviceMagento\Block\Hosted\Form
     */
    protected $form;

    public function setUp()
    {

        $this->paymentConfigMock = $this->getMockBuilder('\HiPay\FullserviceMagento\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->form = $this->objectManagerHelper->getObject(
            'HiPay\FullserviceMagento\Block\Hosted\Form',
            [
                'paymentConfig' => $this->paymentConfigMock
            ]
        );
    }

    /**
     * @cover \HiPay\FullserviceMagento\Block\Hosted\Form::getTemplateFile()
     */
    public function testTemplateFileCanBeRetrieve()
    {
        $this->assertNotNull($this->form->getTemplate());
    }
}
