<?php

namespace HiPay\FullserviceMagento\Model\Method;
class Klarna extends HostedMethod
{
    public const HIPAY_METHOD_CODE = 'hipay_klarna';

    /**
     * @var string
     */
    protected static $_technicalCode = 'klarna';

    /**
     * @var string
     */
    protected $_code = self::HIPAY_METHOD_CODE;
}