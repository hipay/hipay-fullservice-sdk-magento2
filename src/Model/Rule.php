<?php
/*
 * HiPay fullservice SDK
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
namespace HiPay\FullserviceMagento\Model;

/**
 * Hipay Rule data model
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
	 * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
	 */
	protected $_combineFactory;
	
	/**
	 * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
	 */
	protected $_actionCollectionFactory;

	
	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Data\FormFactory $formFactory
	 * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\Data\FormFactory $formFactory,
			\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
			\Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
			\Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
			\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
			\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []
			) {
				parent::__construct($context, $registry,$formFactory,$localeDate, $resource, $resourceCollection, $data);
				
				$this->_combineFactory = $combineFactory;
				$this->_actionCollectionFactory = $actionCollectionFactory;
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
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    
    protected function _getPaymentMethodCode()
    {
    	return str_replace("/", "_", $this->getConfigPath());
    }
	
}
