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

namespace HiPay\FullserviceMagento\Model;

/**
 * Hipay Rule data model
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\Rule _getResource()
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\Rule getResource()
 * @method string getMethodCode()
 * @method \HiPay\FullserviceMagento\Model\Rule setMethodCode(string $value)
 * @method string getConfigPath()
 * @method \HiPay\FullserviceMagento\Model\Rule setConfigPath(string $value)
 * @method string getConditionsSerialized()
 * @method \HiPay\FullserviceMagento\Model\Rule setConditionsSerialized(string $value)
 * @method string getActionsSerialized()
 * @method \HiPay\FullserviceMagento\Model\Rule setActionsSerialized(string $value)
 * @method int getSortOrder()
 * @method \HiPay\FullserviceMagento\Model\Rule setSortOrder(int $value)
 * @method string getSimpleAction()
 * @method \HiPay\FullserviceMagento\Model\Rule setSimpleAction(string $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var \HiPay\FullserviceMagento\Model\Rule\Condition\CombineFactory
     */
    protected $_condCombineFactory;

    /**
     * @var \HiPay\FullserviceMagento\Model\Rule\Condition\Product\CombineFactory
     */
    protected $_condProdCombineF;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context                                      $context
     * @param \Magento\Framework\Registry                                           $registry
     * @param \Magento\Framework\Data\FormFactory                                   $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface                  $localeDate
     * @param \HiPay\FullserviceMagento\Model\Rule\Condition\CombineFactory         $condCombineFactory
     * @param \HiPay\FullserviceMagento\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource               $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb                         $resourceCollection
     * @param array                                                                 $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \HiPay\FullserviceMagento\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \HiPay\FullserviceMagento\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_condCombineFactory = $condCombineFactory;
        $this->_condProdCombineF = $condProdCombineF;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('HiPay\FullserviceMagento\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_condCombineFactory->create()
            ->setMethodCode($this->getMethodCode())
            ->setConfigPath($this->_getHtmlId());
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->_condProdCombineF->create();
    }

    protected function _getHtmlId()
    {
        return str_replace("/", "_", $this->getConfigPath());
    }
}
