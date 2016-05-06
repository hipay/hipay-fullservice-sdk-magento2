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
 * Hipay Card data model
 *
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\Card _getResource()
 * @method \HiPay\FullserviceMagento\Model\ResourceModel\Card getResource()
 * @method int getCustomerId()
 * @method \HiPay\FullserviceMagento\Model\Card setCustomerId(int $customerId)
 * @method string getName()
 * @method \HiPay\FullserviceMagento\Model\Card setName(string $name) 
 * @method string getCcExpMonth()
 * @method \HiPay\FullserviceMagento\Model\Card setCcExpMonth(string $ccExpMonth)
 * @method string getCcExpYear()
 * @method \HiPay\FullserviceMagento\Model\Card setCcExpYear(string $ccExpYear)
 * @method string getCcSecureVerify()
 * @method \HiPay\FullserviceMagento\Model\Card setCcSecureVerify(string $ccSecureVerify)
 * @method string getCclast4()
 * @method \HiPay\FullserviceMagento\Model\Card setCclast4(string $cclast4)
 * @method string getCcOwner()
 * @method \HiPay\FullserviceMagento\Model\Card setCcOwner((string $ccOwner)
 * @method string getCcType()
 * @method \HiPay\FullserviceMagento\Model\Card setCcType(string $ccType)
 * @method string getCcNumberEnc()
 * @method \HiPay\FullserviceMagento\Model\Card setCcNumberEnc(string $ccNumberEnc)
 * @method int getCcStatus()
 * @method \HiPay\FullserviceMagento\Model\Card setCcStatus(int $ccStatus)
 * @method string getCcToken()
 * @method \HiPay\FullserviceMagento\Model\Card setCcToken(int $ccToken)
 * @method int getIsDefault()
 * @method \HiPay\FullserviceMagento\Model\Card setIsDefault(int $isDefault)
 * 
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Card extends \Magento\Framework\Model\AbstractModel
{

	const STATUS_DISABLED = 0;
	const STATUS_ENABLED = 1;
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
			\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
			array $data = []
			) {

				parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}
	
	
  /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('HiPay\FullserviceMagento\Model\ResourceModel\Card');
        $this->setIdFieldName('card_id');
    }
	
}
