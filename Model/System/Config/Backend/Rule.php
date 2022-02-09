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

namespace HiPay\FullserviceMagento\Model\System\Config\Backend;

use Magento\Framework\DataObject;
use Magento\Framework\Phrase;

/**
 * Rule Backend Model
 *
 * @author    Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Rule extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface $_objectManager
     */
    protected $_objectManager;

    /**
     *
     * @var \Magento\Framework\App\RequestInterface $_request
     */
    protected $_request;

    protected $_ruleData = null;

    /**
     * @var \HiPay\FullserviceMagento\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Rule constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $config
     * @param \Magento\Framework\App\Cache\TypeListInterface               $cacheTypeList
     * @param \Magento\Framework\ObjectManagerInterface                    $objectManager
     * @param \Magento\Framework\App\RequestInterface                      $httpRequest
     * @param \HiPay\FullserviceMagento\Model\RuleFactory                  $ruleFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \HiPay\FullserviceMagento\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->_objectManager = $objectManager;
        $this->_request = $httpRequest;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function beforeSave()
    {
        /**
         * @var $rule \HiPay\FullserviceMagento\Model\Rule
         */
        $rule = $this->ruleFactory->create();
        $rule->load($this->getValue());

        if ($errors = $rule->validateData(new DataObject($this->_getRuleData())) !== true) {
            $exception = new \Magento\Framework\Validator\Exception(
                new Phrase(implode(PHP_EOL, $errors))
            );
            foreach ($errors as $errorMessage) {
                $exception->addMessage(new \Magento\Framework\Message\Error($errorMessage));
            }
            throw $exception;
        }

        $rule->setMethodCode($this->_getMethodCode());
        $rule->setConfigPath($this->_getConfigPath());

        $rule->loadPost($this->_getRuleData());

        $rule->save();

        $this->setValue($rule->getId());

        return parent::beforeSave();
    }

    protected function _afterload()
    {

        parent::_afterload();

        /**
         * @var $rule \HiPay\FullserviceMagento\Model\Rule
         */
        $rule = $this->ruleFactory->create();

        if ($this->getValue()) {
            $rule->load($this->getValue());
            if (!$rule->getId()) {
                $rule->setMethodCode($this->_getMethodCode());
                if ($rule->getConfigPath() == "") {
                    $rule->setConfigPath($this->_getConfigPath());
                }
            }
        }

        $this->setRule($rule);

        return $this;
    }

    protected function _getMethodCode()
    {
        list(, $group) = explode("/", $this->getData('path'));
        return $group;
    }

    protected function _getConfigPath()
    {
        return $this->getData('path');
    }

    protected function _getFieldName()
    {
        return str_replace("/", "_", $this->_getConfigPath());
    }

    protected function _getRuleData()
    {
        if ($this->_ruleData === null) {
            $post = $this->_request->getPost();

            $this->_ruleData = array();
            if (isset($post['rule_' . $this->_getFieldName()]['conditions'])) {
                $this->_ruleData['conditions'] = $post['rule_' . $this->_getFieldName()]['conditions'];
            }
        }

        return $this->_ruleData;
    }
}
