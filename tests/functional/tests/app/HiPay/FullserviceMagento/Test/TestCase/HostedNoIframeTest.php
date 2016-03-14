<?php

namespace HiPay\FullserviceMagento\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

class HostedNoIframeTest extends Scenario
{

    /**
     * Runs one page checkout test.
     *
     * @return void
     */
    public function test()
    {
    	
        $this->executeScenario();
    }
}
