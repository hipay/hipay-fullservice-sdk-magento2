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

namespace HiPay\FullserviceMagento\Model\System\Config\Source;

/**
 * Source model for Categories Magento
 *
 * @author    Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class CategoriesMagento implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * CategoriesMagento constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $stores = $this->storeManager->getStores(true);
        $options = [];

        foreach ($stores as $store) {
            $rootId = $store->getRootCategoryId();
            $storeId = $store->getId();

            $collection = $this->getCategoryTree($storeId, $rootId);

            foreach ($collection as $category) {
                $options[] = array(
                    'value' => $category->getId(),
                    'label' =>  $category->getName() . ' (' . $store->getName() . ')'
                );
            }
        }

        return $options;
    }

    /**
     * Get Category Tree
     *
     * @param  int $storeId
     * @param  int $rootId
     * @param  int $level   Level category to select ( Default is 2 actually )
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCategoryTree($storeId, $rootId, $level = 2)
    {
        /**
 * @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
*/
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect('name');
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']);
        $collection->addAttributeToFilter('level', $level);
        $collection->addUrlRewriteToResult();
        $collection->addOrderField('name');
        return $collection;
    }
}
