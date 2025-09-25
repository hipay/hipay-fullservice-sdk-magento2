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
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */

namespace HiPay\FullserviceMagento\Model\Request;

use HiPay\Fullservice\Enum\Transaction\Template;
use HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;

/**
 * Hosted Payment Page Request Object
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class HostedPaymentPage extends Order
{
    /**
     * {@inheritDoc}
     *
     * @see    \HiPay\FullserviceMagento\Model\Request\Order::getRequestObject()
     * @return \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
     */
    public function mapRequest()
    {
        $hppRequest = new HostedPaymentPageRequest();
        $orderRequest = parent::mapRequest();

        foreach (get_object_vars($orderRequest) as $property => $value) {
            $hppRequest->$property = $value;
        }
        //Inherit from parent class Order but no used in this pbject request
        unset($hppRequest->payment_product);

        $hppRequest->css = $this->_config->getValue('css_url');
        $hppRequest->template = ((bool)$this->_config->getValue('iframe_mode') && !$this->_config->isAdminArea()) ?
            'iframe-js' : Template::BASIC_JS ;

        $hppRequest->payment_product_list = implode(",", $this->_config->getPaymentProductsList());

        $hppRequest->payment_product_category_list = implode(",", $this->_config->getPaymentProductCategoryList());

        $hppRequest->time_limit_to_pay = (int)($this->_config->getValue('time_limit_to_pay') * 3600);

        $hppRequest->display_cancel_button =
            $this->_config->getGeneraleValue('cancel_button', 'hipay_hosted_page_management');

        $label = $this->_config->getValue('paypal/button_label');
        $hppRequest->paypal_v2_label = $label === null ? null : $label;
        $shape = $this->_config->getValue('paypal/button_shape');
        $hppRequest->paypal_v2_shape = $shape === null ? null : $shape;
        $color = $this->_config->getValue('paypal/button_color');
        $hppRequest->paypal_v2_color = $color === null ? null : $color;
        $height = $this->_config->getValue('paypal/button_height');
        $hppRequest->paypal_v2_height = ($height === null || (int)$height < 25) ? null : (int)$height;
        $bnpl = $this->_config->getValue('paypal/bnpl');
        $hppRequest->paypal_v2_bnpl = $bnpl === null ? null : (int)$bnpl;

        return $hppRequest;
    }
}
