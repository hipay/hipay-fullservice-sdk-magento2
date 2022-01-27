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

namespace HiPay\FullserviceMagento\Console\Command;

use Magento\Framework\App\ProductMetadataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertSerializedData extends Command
{
    public $input;
    public $output;

    /**
     *
     * @var \HiPay\FullserviceMagento\Model\RuleFactory $_ruleFactory
     */
    protected $ruleFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var
     */
    protected $state;

    public function __construct(
        \HiPay\FullserviceMagento\Model\RuleFactory $ruleFactory,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\State $state
    ) {
        parent::__construct();
        $this->ruleFactory = $ruleFactory;
        $this->productMetadata = $productMetadata;
        $this->state = $state;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('hipay:upgradeToJson')
            ->setDescription('Serialized data to json');
    }

    /**
     * Convert data
     * from serialized to JSON format
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '>')) {
            $this->state->setAreaCode('adminhtml');
            $collection = $this->ruleFactory->create()->getCollection();
            $dataSerialized = false;
            foreach ($collection as $item) {
                $isSerializedConditions = $this->isSerialized($item->getData()["conditions_serialized"]);
                $isSerializedActions = $this->isSerialized($item->getData()["actions_serialized"]);
                if ($isSerializedConditions || $isSerializedActions) {
                    $model = $this->ruleFactory->create();
                    $model->getResource()->load($model, $item->getData()["rule_id"]);
                    if ($isSerializedConditions) {
                        $model->setConditionsSerialized(
                            json_encode(unserialize($item->getData()["conditions_serialized"]))
                        );
                    }
                    if ($isSerializedActions) {
                        $model->setActionsSerialized(
                            json_encode(unserialize($item->getData()["actions_serialized"]))
                        );
                    }
                    $model->save();
                    $dataSerialized = true;
                }
            }

            if ($dataSerialized) {
                $output->writeln("Conversion to Json is done.");
            }
        } else {
            $output->writeln("Your version of magento does not require data conversion.");
        }
    }

    /**
     * Check if value is serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (bool)preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }
}
