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
namespace HiPay\FullserviceMagento\Block\Adminhtml\SplitPayment;

/**
 * Admin SplitPayment edition
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize split payment edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'split_payment_id';
        $this->_blockGroup = 'HiPay_FullserviceMagento';
        $this->_controller = 'adminhtml_splitPayment';

        parent::_construct();

        if ($this->_isAllowedAction('HiPay_FullserviceMagento::split_save')) {

            $this->buttonList->update('save', 'label', __('Save Split Payment'));
            $this->buttonList->update('save', 'class', 'save secondary');
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('HiPay_FullserviceMagento::split_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Split Payment'));
        } else {
            $this->buttonList->remove('delete');
        }

        if ($this->_isAllowedAction('HiPay_FullserviceMagento::split_pay') && $this->_coreRegistry->registry('split_payment')->canPay()) {
            $this->buttonList->add(
                'pay',
                [
                    'label' => __('Pay'),
                    'class' => 'run primary',
                    'onclick' => 'confirmSetLocation(\'' . __(
                            'Are you sure you want to do this?'
                        ) . '\', \'' . $this->getPayUrl() . '\')'
                ],
                1
            );
        } else {
            $this->buttonList->remove('pay');
        }


    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('split_payment')->getId()) {
            return __("Edit Split Payment '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('split_payment')->getName()));
        } else {
            return __('New Split Payment');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return string
     */
    public function getPayUrl()
    {
        return $this->getUrl('*/*/pay', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit']);
    }
}
