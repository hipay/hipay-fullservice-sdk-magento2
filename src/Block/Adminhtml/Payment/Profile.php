<?php 
/*
 * HiPay fullservice Magento2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Block\Adminhtml\Payment;

/**
 * Adminhtml payment profiles block
 *
 */
class Profile extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_payment_profile';
        $this->_blockGroup = 'HiPay_FullserviceMagento';
        $this->_headerText = __('Payment Profiles');
        $this->_addButtonLabel = __('Create New Paymet Profiles');
        parent::_construct();
       /* if (!$this->_authorization->isAllowed('HiPay_FullserviceMagento::create')) {
            $this->buttonList->remove('add');
        }*/
    }

    /**
     * Retrieve url for order creation
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('hipay/payment_profile/create');
    }
}
