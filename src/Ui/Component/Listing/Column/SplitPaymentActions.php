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
namespace HiPay\FullserviceMagento\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

/**
 * Class SplitPaymentActions
 */
class SplitPaymentActions extends Column
{
    /** Url path */
    const SPLIT_URL_PATH_EDIT = 'hipay/splitpayment/edit';
    const SPLIT_URL_PATH_DELETE = 'hipay/splitpayment/delete';
    const SPLIT_URL_PATH_PAY = 'hipay/splitpayment/pay';

    /** @var UrlBuilder */
    protected $actionUrlBuilder;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::SPLIT_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['split_payment_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['split_payment_id' => $item['split_payment_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::PROFILE_URL_PATH_DELETE, ['split_payment_id' => $item['split_payment_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.name }'),
                            'message' => __('Are you sure you wan\'t to delete a ${ $.$data.name } record?')
                        ]
                    ];
                    $item[$name]['pay'] = [
                    		'href' => $this->urlBuilder->getUrl(self::PROFILE_URL_PATH_PAY, ['split_payment_id' => $item['split_payment_id']]),
                    		'label' => __('Pay'),
                    		'confirm' => [
                    				'title' => __('Pay ${ $.$data.amount_to_pay }'),
                    				'message' => __('Are you sure you wan\'t to pay ${ $.$data.amount_to_pay } ?')
                    		]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
