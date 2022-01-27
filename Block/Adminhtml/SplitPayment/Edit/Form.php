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

namespace HiPay\FullserviceMagento\Block\Adminhtml\SplitPayment\Edit;

/**
 * Adminhtml split payment edit form block
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     *
     * @var \HiPay\FullserviceMagento\Model\System\Config\Source\SplitPayment\Status $spStatus
     */
    protected $spStatus;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \HiPay\FullserviceMagento\Model\System\Config\Source\SplitPayment\Status $spStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \HiPay\FullserviceMagento\Model\System\Config\Source\SplitPayment\Status $spStatus,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->spStatus = $spStatus;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('HiPay_FullserviceMagento::split_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('splitpayment_');

        $model = $this->_coreRegistry->registry('split_payment');

        $fieldset = $form->addFieldset(
            'splitpayment_fieldset',
            ['legend' => __('Split Payment'), 'class' => 'fieldset-wide']
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );

        $fieldset->addField(
            'date_to_pay',
            'date',
            [
                'name' => 'date_to_pay',
                'label' => __('Date to pay'),
                'title' => __('Date to pay'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'date_format' => $dateFormat,
                'class' => 'validate-date validate-date-range'
            ]
        );

        $fieldset->addField(
            'amount_to_pay',
            'text',
            [
                'name' => 'amount_to_pay',
                'label' => __('Amount to pay'),
                'title' => __('Amount to pay'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-zero-or-greater'
            ]
        );

        $options = $this->spStatus->toOptionArray();
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $options,
                'required' => true,
                'disabled' => true
            ]
        );

        $this->_eventManager->dispatch('adminhtml_hipay_splitpayment_edit_prepare_form', ['form' => $form]);

        if ($model->getSplitPaymentId() !== null) {
            // If edit add id
            $form->addField(
                'split_payment_id',
                'hidden',
                ['name' => 'split_payment_id', 'value' => $model->getSplitPaymentId()]
            );
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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
}
