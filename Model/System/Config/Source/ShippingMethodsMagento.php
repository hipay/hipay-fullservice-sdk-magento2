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
namespace HiPay\FullserviceMagento\Model\System\Config\Source;

/**
 * Source model for Magento
 *
 * @package HiPay\FullserviceMagento
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */

class ShippingMethodsMagento implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_config_shipping;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * ShippingMethodsMagento constructor.
     *
     * @param \Magento\Shipping\Model\Config $configShipping
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Shipping\Model\Config $configShipping,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_config_shipping = $configShipping;
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Return Shipping methods available in all store
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $carriers = $this->_config_shipping->getActiveCarriers();
        foreach ($carriers as $carrier) {
            $methods = $carrier->getAllowedMethods();
            foreach ($methods as $code => $method) {
                if (is_object($method)) {
                    $options[] = array(
                        'value' => $carrier->getId() . '_' . $code,
                        'label' => $carrier->getId() . ' - ' . $method->getText()
                    );
                } else {
                    if (!empty($method)) {
                        $options[] = array(
                            'value' => $carrier->getId() . '_' . $code,
                            'label' => $carrier->getId() . ' - ' . $method
                        );
                    }
                }
            }
        }

        $options[] = array(
            'value' => 'hipay_shipping_custom',
            'label' => __('Custom shipping code')
        );

        return $options;
    }

    public function getCarriers()
    {
        $carriersArray = [];
        $carriers = $this->_config_shipping->getActiveCarriers();
        foreach ($carriers as $carrier) {
            $carrierTitle = $this->_scopeConfig->getValue(
                'carriers/' . $carrier->getId() . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $carriersArray[] = array(
                'label' => $carrierTitle,
                'code' => $carrier->getId()
            );
        }

        return $carriersArray;
    }
}
