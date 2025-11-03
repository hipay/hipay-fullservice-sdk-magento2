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

namespace HiPay\FullserviceMagento\Block\Cc;

use HiPay\FullserviceMagento\Model\Config\Factory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;

/**
 * Block CC from
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Form extends \Magento\Payment\Block\Form\Cc
{
    /**
     * @var string
     */
    protected $_template = 'HiPay_FullserviceMagento::form/cc.phtml';

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\Config $_hipayConfig
     */
    protected $_hipayConfig;

    /**
     *
     * @var Factory $configFactory
     */
    protected $configFactory;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Config  $paymentConfig
     * @param Factory $configFactory
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Factory $configFactory,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->configFactory = $configFactory;
    }

    /**
     * Retrieve HiPay configuration
     *
     * @return \HiPay\FullserviceMagento\Model\Config
     */
    public function getConfig()
    {
        if ($this->_hipayConfig === null) {
            $this->_hipayConfig = $this->configFactory->create(['params' => ['methodCode' => $this->getMethodCode()]]);
        }

        return $this->_hipayConfig;
    }

    /**
     * Whether switch/solo card type available
     *
     * @return bool
     */
    public function hasSsCardType()
    {
        $availableTypes = explode(',', $this->getMethod()->getConfigData('cctypes') ?: '');
        $ssPresenations = array_intersect(['SS', 'SO'], $availableTypes);
        if ($availableTypes && !empty($ssPresenations)) {
            return true;
        }
        return false;
    }

    /**
     * Get current API environment
     *
     * @return string|null
     */
    public function getEnv()
    {
        return $this->getConfig()->getApiEnv();
    }

    /**
     * Get API username from configuration
     *
     * @return mixed|string
     */
    public function getApiUsername()
    {
        return $this->getConfig()->getApiUsername();
    }

    /**
     * Get API password from configuration
     *
     * @return mixed|string
     */
    public function getApiPassword()
    {
        return $this->getConfig()->getApiPassword();
    }

    /**
     * Get SDK JavaScript URL
     *
     * @return bool
     */
    public function getSdkJsUrl()
    {
        return $this->getConfig()->getSdkJsUrl();
    }
}
