<?php

/**
 * HiPay fullservice Magento2
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

namespace HiPay\FullserviceMagento\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Block sortable checkboxes
 * used for 3ds and oneclick on payment methods configuration
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class SyncButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'HiPay_FullserviceMagento::system/config/form/field/sync_button.phtml';

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'hashing_algorithm_button',
                'label' => __('Synchronize Hashing algorithm'),
                'onclick' =>
                    'deleteConfirm(\''
                    . __('Are you sure you want to sync the hashing configuration for notifications ?')
                    . '\',\'' . $this->getButtonAction() . '\')',
            ]
        );

        return $button->toHtml();
    }

    /**
     * Get Path for Synchronize Action
     *
     * @return string
     * @type url
     *
     */
    public function getButtonAction()
    {
        return $this->getUrl(
            'hipay/hashing/synchronize',
            [
                'store' => $this->getRequest()->getParam('store'),
                'website' => $this->getRequest()->getParam('website')
            ]
        );
    }
}
