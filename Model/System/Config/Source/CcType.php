<?php
/**
 * HiPay Fullservice Magento
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
namespace HiPay\FullserviceMagento\Model\System\Config\Source;

/**
 * CcType source model
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CcType extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Allowed CC types
     *
     * @var array
     */
    protected $_allowedTypes = [];

    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * Fullservice config model
     *
     * @var \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct
     */
    protected $_paymentProductSource;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_codeToLabel = ['VI' => 'Visa/Carte bleue', 'MI' => 'Maestro/Bancontact'];

    /**
     * CcType constructor.
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param PaymentProduct $paymentProductSource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Payment\Model\Config $paymentConfig,
        \HiPay\FullserviceMagento\Model\System\Config\Source\PaymentProduct $paymentProductSource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_paymentConfig = $paymentConfig;
        $this->_paymentProductSource = $paymentProductSource;
        $this->_scopeConfig = $scopeConfig;

        $this->_allowedTypes = ['VI', 'MC', 'AE', 'MI'];
    }

    /**
     * Return allowed cc types for current method
     *
     * @return array
     */
    public function getAllowedTypes()
    {
        return $this->_allowedTypes;
    }

    /**
     * Setter for allowed types
     *
     * @param array $values
     * @return $this
     */
    public function setAllowedTypes(array $values)
    {
        $this->_allowedTypes = $values;
        return $this;
    }

    public function toKeyValue($withCustomLabel = false)
    {

        /**
         * making filter by allowed cards
         */
        $allowed = $this->getAllowedTypes();
        $options = [];

        //populate options with allowed natives cc types
        foreach ($this->_paymentConfig->getCcTypes() as $code => $name) {
            if (in_array($code, $allowed) || empty($allowed)) {
                if ($withCustomLabel && isset($this->_codeToLabel[$code])) {
                    $name = $this->_codeToLabel[$code];
                } elseif (strpos(strtolower($name), "maestro") !== false
                ) {
                    //Special case due to wrong comparison in
                    // magento/module-payment/view/frontend/web/js/model/credit-card-validation/validator.js Line 36
                    $name = "Maestro";
                }
                $options[$code] = ['value' => $code, 'label' => $name];
            }
        }

        //populate options with allowed fullservice payment methods
        foreach ($this->_paymentProductSource->toOptionArray() as $option) {
            if (in_array($option['value'], $allowed) || empty($allowed)) {
                $options[$option['value']] = $option;
            }
        }

        $ordered = array();

        if ($this->getPath()) {
            list($section_locale, $method, $field) = explode("/", $this->getPath());
            list($section) = explode("_", $section_locale);

            $configData = $this->_scopeConfig->getValue(implode("/", [$section, $method, $field]));

            $availableTypes = explode(",", $configData);

            foreach ($availableTypes as $key) {
                if (array_key_exists($key, $options)) {
                    $ordered[$key] = $options[$key];
                    unset($options[$key]);
                }
            }
        }

        return array_merge($ordered, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->toKeyValue(true);
    }
}
