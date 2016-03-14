<?php
namespace HiPay\FullserviceMagento\Test\Unit\Block\Hosted;

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
	public function testTemplateFileCanBeRetrieve(){
		$this->assertNotNull($this->form->getTemplate());
	
	}
	
	
}
