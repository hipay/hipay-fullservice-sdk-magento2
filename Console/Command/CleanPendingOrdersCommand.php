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

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use HiPay\FullserviceMagento\Cron\CleanPendingOrders;

/**
 * Class ConvertSerializedData
 * Add json serialization command
 */
class CleanPendingOrdersCommand extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var CleanPendingOrders
     */
    protected $cleanPendingOrders;

    /**
     * Constructor
     *
     * @param CleanPendingOrders $cleanPendingOrders
     */
    public function __construct(State $state, CleanPendingOrders $cleanPendingOrders)
    {
        $this->state = $state;
        $this->cleanPendingOrders = $cleanPendingOrders;
        parent::__construct();

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('global');
        $this->cleanPendingOrders->execute();
        $output->writeln('<info>Pending orders cleaned successfully.</info>');
        return Command::SUCCESS;
    }
}
