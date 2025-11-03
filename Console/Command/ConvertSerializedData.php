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

namespace HiPay\FullserviceMagento\Console\Command;

use HiPay\FullserviceMagento\Model\RuleFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Serialize\SerializerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConvertSerializedData
 * Add json serialization command
 */
class ConvertSerializedData extends Command
{
    /**
     * @var InputInterface
     */
    public $input;
    /**
     * @var OutputInterface
     */
    public $output;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param RuleFactory              $ruleFactory
     * @param ProductMetadataInterface $productMetadata
     * @param State                    $state
     * @param SerializerInterface      $serializer
     */
    public function __construct(
        RuleFactory $ruleFactory,
        ProductMetadataInterface $productMetadata,
        State $state,
        SerializerInterface $serializer
    ) {
        parent::__construct();
        $this->ruleFactory = $ruleFactory;
        $this->productMetadata = $productMetadata;
        $this->state = $state;
        $this->serializer = $serializer;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('hipay:upgradeToJson')
            ->setDescription('Serialized data to json');
    }

    /**
     * Convert data from serialized to JSON format
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '>')) {
            $this->state->setAreaCode('adminhtml');
            $collection = $this->ruleFactory->create()->getCollection();
            $dataSerialized = false;

            foreach ($collection as $item) {
                $conditions = $item->getData('conditions_serialized');
                $actions = $item->getData('actions_serialized');

                $isSerializedConditions = $this->isSerialized($conditions);
                $isSerializedActions = $this->isSerialized($actions);

                if ($isSerializedConditions || $isSerializedActions) {
                    $model = $this->ruleFactory->create()->load($item->getId());

                    if ($isSerializedConditions) {
                        $decodedConditions = $this->serializer->unserialize($conditions);
                        $model->setConditionsSerialized(json_encode($decodedConditions));
                    }

                    if ($isSerializedActions) {
                        $decodedActions = $this->serializer->unserialize($actions);
                        $model->setActionsSerialized(json_encode($decodedActions));
                    }

                    $model->save();
                    $dataSerialized = true;
                }
            }

            if ($dataSerialized) {
                $output->writeln("Conversion to Json is done.");
            } else {
                $output->writeln("No serialized data found to convert.");
            }
        } else {
            $output->writeln("Your version of Magento does not require data conversion.");
        }

        return Command::SUCCESS;
    }

    /**
     * Check if value is serialized string
     *
     * @param  string $value
     * @return bool
     */
    private function isSerialized($value): bool
    {
        return is_string($value)
            && (bool)preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }
}
