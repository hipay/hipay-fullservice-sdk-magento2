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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\PaymentProfile;

use Magento\Backend\App\Action;

/**
 * Save payment profile
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \HiPay\FullserviceMagento\Model\PaymentProfile\Factory
     */
    private $paymentProfileFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \HiPay\FullserviceMagento\Model\PaymentProfile\Factory $paymentProfileFactory
     */
    public function __construct(
        Action\Context $context,
        \HiPay\FullserviceMagento\Model\PaymentProfile\Factory $paymentProfileFactory
    ) {
        $this->paymentProfileFactory = $paymentProfileFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if ((int)$data['period_max_cycles'] < 1) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Period max cycles is equals zero or negative ")
                );
            }

            if ((int)$data['period_frequency'] < 1) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Period frequency is equals zero or negative for Payment Profile ID: %s")
                );
            }

            $data['payment_type'] = \HiPay\FullserviceMagento\Model\PaymentProfile::PAYMENT_TYPE_SPLIT;

            $model = $this->paymentProfileFactory->create();

            $id = $this->getRequest()->getParam('profile_id');
            if ($id) {
                $model->getResource()->load($model, $id);
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'hipay_paymentprofile_prepare_save',
                ['paymentprofile' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->getResource()->save($model);
                $this->messageManager->addSuccess(__('You saved this payment profile.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['profile_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the payment profile.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('profile_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
