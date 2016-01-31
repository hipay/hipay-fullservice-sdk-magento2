<?php
/*
 * Hipay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - Hipay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace Hipay\FullserviceMagento\Controller\Hosted;

use Magento\Checkout\Model\Type\Onepage;
class Start extends \Hipay\FullserviceMagento\Controller\Fullservice
{	
	/**
	 * 
	 * @var \Hipay\FullserviceMagento\Model\Checkout\Hosted\Checkout
	 */
	protected $_checkout;
	
	/**
	 * Checkout mode type
	 *
	 * @var string
	 */
	protected $_checkoutType = 'Hipay\FullserviceMagento\Model\Checkout\Hosted\Checkout';

	
	/**
	 * Config mode type
	 *
	 * @var string
	 */
	protected $_configType = 'Hipay\FullserviceMagento\Model\Config';
	
	/**
	 * Config method type
	 *
	 * @var string
	 */
	protected $_configMethod = \Hipay\FullserviceMagento\Model\HostedMethod::HIPAY_HOSTED_METHOD_CODE;
	
    /**
     * Start 
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        try {
            $this->_initCheckout();

            if ($this->_getQuote()->getIsMultiShipping()) {
                $this->_getQuote()->setIsMultiShipping(false);
                $this->_getQuote()->removeAllAddresses();
            }

         	$customerData = $this->_customerSession->getCustomerDataObject();
            $quoteCheckoutMethod = $this->_getQuote()->getCheckoutMethod();
            if ($customerData->getId()) {
                $this->_checkout->setCustomerWithAddressChange(
                    $customerData,
                    $this->_getQuote()->getBillingAddress(),
                    $this->_getQuote()->getShippingAddress()
                );
            } elseif ((!$quoteCheckoutMethod || $quoteCheckoutMethod != Onepage::METHOD_REGISTER)
                && !$this->_objectManager->get('Magento\Checkout\Helper\Data')->isAllowedGuestCheckout(
                    $this->_getQuote(),
                    $this->_getQuote()->getStoreId()
                )
            ) {
                $this->messageManager->addNoticeMessage(
                    __('To check out, please sign in with your email address.')
                );

                $this->_objectManager->get('Magento\Checkout\Helper\ExpressRedirect')->redirectLogin($this);
                $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', ['_current' => true]));

                return;
            }
            
            
            
            $url = $this->_checkout->start();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t start Hipay Hosted Payment.')
            );
        }

        $this->_redirect('checkout/cart');
    }
}
