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
namespace HiPay\FullserviceMagento\Controller\Adminhtml\Rule;

use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Add new condition html on rule edition
 * Used for 3ds and oneclick in payment configuration
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class NewConditionHtml extends \Magento\Backend\App\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $html = '';
        $marker = array();

        if (preg_match('/_([a-z0-9_]*)--/', $id, $marker)) {
            $customId = $marker[1];
            $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
            $type = $typeArr[0];
            list($section, $m1, $m2) = explode('_', $customId);
            $methodCode = $m1 . '_' . $m2;
            $field = substr($customId, (strpos($customId, $m2 . '_') + strlen($m2 . '_')));
            $configPath = implode('/', array($section, $methodCode, $field));

            $model = $this->_objectManager->create(
                $type
            )->setId(
                str_replace('_' . $customId, "", $id)
            )->setType(
                $type
            )->setRule(
                $this->_objectManager->create('HiPay\FullserviceMagento\Model\Rule')
            )->setPrefix(
                'conditions'
            )
                ->setMethodCode($methodCode)
                ->setConfigPath(str_replace("/", "_", $configPath));
            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            if ($model instanceof AbstractCondition) {
                $model->setJsFormObject($this->getRequest()->getParam('form'));
                $html = $model->asHtmlRecursive();
            }
        }
        $this->getResponse()->setBody($html);
    }
}
