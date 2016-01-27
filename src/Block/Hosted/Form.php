<?php
namespace Hipay\FullserviceMagento\Block\Hosted;

/**
 *
 * @author kassim
 *        
 */
class Form extends \Magento\Payment\Block\Form {
	
	/**
	 * @var string
	 */
	protected $_template = 'Hipay_Fullservice::form/hosted.phtml';

	
	/**
	 * Payment config model
	 *
	 * @var \Magento\Payment\Model\Config
	 */
	protected $_paymentConfig;
	
	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Payment\Model\Config $paymentConfig
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\View\Element\Template\Context $context,
			\Magento\Payment\Model\Config $paymentConfig,
			array $data = []
			) {
				parent::__construct($context, $data);
				$this->_paymentConfig = $paymentConfig;
	}
	
	
	
}
