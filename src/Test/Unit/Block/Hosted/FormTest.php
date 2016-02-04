<?php
namespace Hipay\FullserviceMagento\Test\Unit\Block\Hosted;

class FormTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $paymentConfigMock;
	

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $contextMock;
	
	/**
	 * @var \Hipay\FullserviceMagento\Block\Hosted\Form
	 */
	protected $form;
	
	public function setUp()
	{
		
		$this->contextMock->expects($this->once())
		->method('getAppState')
		->willReturn($this->appStateMock);
		
		$this->paymentConfigMock = $this->getMockBuilder('\Magento\Payment\Model\Config')
		->disableOriginalConstructor()
		->getMock();
		
		$this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
		$this->form = $this->objectManagerHelper->getObject(
				'Hipay\FullserviceMagento\Block\Hosted\Form',
				[
						'context' => $this->contextMock,
						'paymentConfig' => $this->paymentConfigMock
				]
				);
	}
	
	/**
	 * @cover \Hipay\FullserviceMagento\Block\Hosted\Form::_getTemplateFile
	 */
	public function testTemplateFileCanBeRetrieve(){
	
		$this->assertNotEmpty($this->form->getTemplateFile());
	
	}
	
	
}
