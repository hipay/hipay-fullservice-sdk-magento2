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

use HiPay\FullserviceMagento\Cron\CleanPendingOrders;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConvertSerializedData
 * Add json serialization command
 */
class CleanPendingOrdersCommand extends Command
{
    /**
     * @var CleanPendingOrders
     */
    protected $cleanPendingOrders;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var AreaList
     */
    protected $_areaList;

    /**
     * Constructor
     *
     * @param State              $state
     * @param AreaList           $areaList
     * @param CleanPendingOrders $cleanPendingOrders
     */
    public function __construct(
        State $state,
        AreaList $areaList,
        CleanPendingOrders $cleanPendingOrders
    ) {
        parent::__construct();
        $this->cleanPendingOrders = $cleanPendingOrders;
        $this->_state = $state;
        $this->_areaList = $areaList;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('hipay:clean:pendingorders')
            ->setDescription('Manually trigger the clean pending orders cron job.');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode(Area::AREA_FRONTEND);
        $areaModel = $this->_areaList->getArea($this->_state->getAreaCode());
        // Load design and translation parts
        $areaModel->load(AreaInterface::PART_DESIGN);
        $areaModel->load(AreaInterface::PART_TRANSLATE);

        $this->cleanPendingOrders->execute();
        $output->writeln('<info>Pending orders cleaned successfully.</info>');
        return 1;
    }
}
