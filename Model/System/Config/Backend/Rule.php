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

use HiPay\FullserviceMagento\Model\RuleFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception;

/**
 * Rule Backend Model
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Rule extends Value
{
    /**
     *
     * @var RequestInterface $_request
     */
    protected $_request;

    /**
     * @var array|mixed|null
     */
    protected $_ruleData = null;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param RequestInterface $httpRequest
     * @param RuleFactory $ruleFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                     $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface        $config,
        TypeListInterface           $cacheTypeList,
        RequestInterface            $httpRequest,
        RuleFactory                 $ruleFactory,
        AbstractResource            $resource = null,
        AbstractDb                  $resourceCollection = null,
        array                       $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->_request = $httpRequest;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     * @throws Exception|LocalizedException
     */
    public function beforeSave()
    {
        /**
         * @var $rule \HiPay\FullserviceMagento\Model\Rule
         */
        $rule = $this->ruleFactory->create();
        $rule->load($this->getValue());

        if ($errors = $rule->validateData(new DataObject($this->_getRuleData())) !== true) {
            $exception = new Exception(
                new Phrase(implode(PHP_EOL, $errors))
            );
            foreach ($errors as $errorMessage) {
                $exception->addMessage(new Error($errorMessage));
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

    /**
     * Load and initialize rule model after config value is loaded
     *
     * @return $this|Rule
     * @throws LocalizedException
     */
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

    /**
     * Extract method code from config path
     *
     * @return mixed|string
     */
    protected function _getMethodCode()
    {
        list(, $group) = explode("/", $this->getData('path') ?: '');
        return $group;
    }

    /**
     * Return raw config path.
     *
     * @return array|mixed|null
     */
    protected function _getConfigPath()
    {
        return $this->getData('path');
    }

    /**
     * Convert config path to field name format using underscores
     *
     * @return array|mixed|string|string[]|null
     */
    protected function _getFieldName()
    {
        return str_replace("/", "_", $this->_getConfigPath());
    }

    /**
     * Retrieve rule conditions from POST data for current field
     *
     * @return array|mixed|null
     */
    protected function _getRuleData()
    {
        if ($this->_ruleData === null) {
            $post = $this->_request->getPost();

            $this->_ruleData = [];
            if (isset($post['rule_' . $this->_getFieldName()]['conditions'])) {
                $this->_ruleData['conditions'] = $post['rule_' . $this->_getFieldName()]['conditions'];
            }
        }

        return $this->_ruleData;
    }
}
