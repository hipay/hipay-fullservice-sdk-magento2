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

/**
 * Block CC from
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
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
     * @var \HiPay\FullserviceMagento\Model\Config\Factory $configFactory
     */
    protected $configFactory;

    /**
     * Form constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config                    $paymentConfig
     * @param \HiPay\FullserviceMagento\Model\Config\Factory   $configFactory
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \HiPay\FullserviceMagento\Model\Config\Factory $configFactory,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->configFactory = $configFactory;
    }

    /**
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
        $availableTypes = explode(',', $this->getMethod()->getConfigData('cctypes'));
        $ssPresenations = array_intersect(['SS', 'SO'], $availableTypes);
        if ($availableTypes && !empty($ssPresenations)) {
            return true;
        }
        return false;
    }

    public function getEnv()
    {
        return $this->getConfig()->getApiEnv();
    }

    public function getApiUsername()
    {
        return $this->getConfig()->getApiUsername();
    }

    public function getApiPassword()
    {
        return $this->getConfig()->getApiPassword();
    }

    public function getSdkJsUrl()
    {
        return $this->getConfig()->getSdkJsUrl();
    }
}
