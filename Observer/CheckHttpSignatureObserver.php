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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */
namespace HiPay\FullserviceMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use HiPay\FullserviceMagento\Model\Config\Factory as ConfigFactory;


/**
 * HiPay module observer
 * 
 * Check http signature from TPP notification
 * 
 * Redirections haven't checked because http params can be not present (Depend of TPP config)
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CheckHttpSignatureObserver implements ObserverInterface
{
	protected $_actionsToCheck = [
			/*'hipay_redirect_accept',
			'hipay_redirect_cancel',
			'hipay_redirect_decline',
			'hipay_redirect_exception',*/
			'hipay_notify_index'
	];
	
	/**
	 *
	 * @var \Magento\Sales\Model\OrderFactory $_orderFactory
	 */
	protected $_orderFactory;
	
	/**
	 * 
	 * @var ConfigFactory
	 */
	protected $_configFactory;

    /**
     * Constructor
     *
     */
    public function __construct(
			 \Magento\Sales\Model\OrderFactory $orderFactory,
    		ConfigFactory $configFactory
    ) {
		$this->_orderFactory = $orderFactory;
		$this->_configFactory = $configFactory;
    }

    /**
     * Check if signature and dispatch only if is valid
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
    	/** @var $controller \HiPay\FullserviceMagento\Controller\Fullservice */
    	$controller = $observer->getControllerAction();
    	/** @var $request \Magento\Framework\App\Request\Http */
    	$request = $observer->getRequest();
    	
    	if(in_array($request->getFullActionName(),$this->_actionsToCheck)){
    		try {
    			$orderId = $this->getOrderId($request);
	    		$order = $this->_orderFactory->create()->loadByIncrementId($orderId);

	    		if(!$order->getId()){
	    			throw new \Exception("Order not found for id: " . $orderId);
	    		}
	    		/** @var $config \HiPay\FullserviceMagento\Model\Config */
	    		$config = $this->_configFactory->create(['params'=>['methodCode'=>$order->getPayment()->getMethod(),'storeId'=>$order->getStoreId(),'order'=>$order]]);
	    		$secretPassphrase = $config->getSecretPassphrase();
	    		if(!\HiPay\Fullservice\Helper\Signature::isValidHttpSignature($secretPassphrase)){
		    		$controller->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
		    		$controller->getResponse()->setBody("Wrong Secret Signature!");
	    		}

    		} catch (\Exception $e) {
    			$controller->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
    			$controller->getResponse()->setBody("Exception during check signature.");
				$controller->getResponse()->setHttpResponseCode(500);
    		}
    	}
    	
    	
        return $this;
    }
    
    /**
     * 
     * @param \Magento\Framework\App\Request\Http $request
     */
    protected function getOrderId(\Magento\Framework\App\RequestInterface $request){
    	$orderId = 0;
    	if($request->getParam('orderid',0)){ //Redirection case
    		$orderId = $request->getParam('orderid',0);
    	}
    	elseif(($o = $request->getParam('order',[])) && isset($o['id'])){

			$orderId = $o['id'];

			if (strpos($o['id'], '-split-') !== false) {
				return explode("-", $o['id'])[0];
			}

    	}
    	return $orderId;
    	
    }
    
    
}
