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

namespace HiPay\FullserviceMagento\Model\Config;

/**
 * Factory class for payment config
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Factory
{
    public const PRODUCTION = "production";

    public const PRODUCTION_MOTO = "production_moto";

    public const PRODUCTION_APPLEPAY = "applepay";

    public const STAGE = "stage";

    public const STAGE_MOTO = "stage_moto";

    public const STAGE_APPLEPAY = "stage_applepay";

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var string
     */
    protected $_configClassName = \HiPay\FullserviceMagento\Model\Config::class;

    /**
     * @var array
     */
    protected $_config = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
        $this->_config = [
            self::PRODUCTION => [
                'forceMoto' => false,
                'forceStage' => false
            ],
            self::PRODUCTION_MOTO => [
                'forceMoto' => true,
                'forceStage' => false
            ],
            self::PRODUCTION_APPLEPAY => [
                'forceMoto' => false,
                'forceStage' => false,
                'isApplePay' => true
            ],
            self::STAGE => [
                'forceMoto' => false,
                'forceStage' => true
            ],
            self::STAGE_MOTO => [
                'forceMoto' => true,
                'forceStage' => true
            ],
            self::STAGE_APPLEPAY => [
                'forceMoto' => false,
                'forceStage' => true,
                'isApplePay' => true
            ],
        ];
    }

    /**
     * Create class instance with specified parameters
     *
     * @param  array $data
     * @return mixed
     */
    public function create(array $data = [])
    {
        if (isset($data['params']['platform'])) {
            $data['params'] = array_merge($data['params'], $this->_getPlatformConfig($data['params']['platform']));
        }
        return $this->_objectManager->create($this->_configClassName, $data);
    }

    /**
     * Retrieve configuration array for the specified platform key.
     *
     * @param  string $platform
     * @return array
     */
    protected function _getPlatformConfig($platform)
    {
        return (isset($this->_config[$platform])) ? $this->_config[$platform] : [];
    }
}
