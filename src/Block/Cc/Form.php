<?php
namespace HiPay\FullserviceMagento\Block\Cc;

/**
 *
 * @author kassim
 *        
 */
class Form extends \Magento\Payment\Block\Form\Cc {
	
	/**
	 * @var string
	 */
	protected $_template = 'HiPay_FullserviceMagento::form/cc.phtml';
	
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
	
	/**
	 * Whether switch/solo card type available
	 *
	 * @return bool
	 */
	public function hasSsCardType()
	{
		$availableTypes = explode(',', $this->getMethod()->getConfigData('cctypes'));
		$ssPresenations = array_intersect(['SS', 'SO'], $availableTypes);
		if ($availableTypes && count($ssPresenations) > 0) {
			return true;
		}
		return false;
	}
	
}
