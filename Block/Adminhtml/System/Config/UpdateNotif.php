<?php

/**
 * HiPay fullservice Magento2
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

namespace HiPay\FullserviceMagento\Block\Adminhtml\System\Config;

use DateTime;
use HiPay\FullserviceMagento\Helper\Data;
use HiPay\FullserviceMagento\Helper\Notification;
use HiPay\FullserviceMagento\Model\Config;
use HiPay\FullserviceMagento\Model\Config\Factory;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\HTTP\Client\Curl;

/**
/**
 * Update notification block
 *
 * @copyright Copyright (c) 2016 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link      https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class UpdateNotif implements \Magento\Framework\Notification\MessageInterface
{
    private const DATE_FORMAT = 'd/m/Y H:i:s';

    protected const HIPAY_GITHUB_MAGENTO2_LATEST =
        "https://api.github.com/repos/hipay/hipay-fullservice-sdk-magento2/releases/latest";

    protected const MESSAGE_IDENTITY = 'HipPay Version Notification';

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var Data $_helper
     */
    protected $_helper;

    /**
     * @var Notification $_notifHelper
     */
    protected $_notifHelper;

    /**
     * @var InboxFactory $_inbox
     */
    protected $_inbox;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var DateTime $lastGithubPoll Last time gitHub API was called
     */
    private $lastGithubPoll;

    /**
     * @var String $version Current module version
     */
    private $version;

    /**
     * @var String $newVersion Latest version available
     */
    private $newVersion;

    /**
     * @var String $newVersionDate Publication date of the latest version
     */
    private $newVersionDate;

    /**
     * @var String $readMeUrl URL targeting the latest version's ReadMe on GitHub
     */
    private $readMeUrl;

    /**
     * @param Session      $authSession
     * @param Data         $hipayHelper
     * @param Notification $hipayNotificationHelper
     * @param InboxFactory $inboxFactory
     * @param Factory      $configFactory
     * @param Curl         $curl
     */
    public function __construct(
        Session      $authSession,
        Data         $hipayHelper,
        Notification $hipayNotificationHelper,
        InboxFactory $inboxFactory,
        Factory      $configFactory,
        Curl         $curl
    ) {
        $this->_authSession = $authSession;
        $this->_helper = $hipayHelper;
        $this->_notifHelper = $hipayNotificationHelper;
        $this->_inbox = $inboxFactory;
        $this->_config = $configFactory->create();
        $this->curl = $curl;
    }

    /**
     * Reads the update info from saved configuration data
     */
    public function readFromConf()
    {
        $lastResult = $this->_helper->readVersionDataFromConf($this->_config);

        $this->version = $lastResult->version;

        // If conf exists, reading from it
        if (isset($lastResult->newVersion)) {
            $this->newVersion = $lastResult->newVersion;
            $this->newVersionDate = $lastResult->newVersionDate;
            $this->readMeUrl = $lastResult->readMeUrl;
            /*
             * GitHub limits calls over 60 per hour per IP
             * https://developer.github.com/v3/#rate-limiting
             *
             * Solution : max 1 call per hour
             */
            $this->lastGithubPoll = DateTime::createFromFormat(self::DATE_FORMAT, $lastResult->lastCall);

            // If not, setting default data with values not showing the block
        } else {
            $this->newVersion = $this->version;
            $this->newVersionDate = DateTime::createFromFormat(self::DATE_FORMAT, "01/01/1990 00:00:00");
            $this->readMeUrl = "#";
            $this->lastGithubPoll = DateTime::createFromFormat(self::DATE_FORMAT, "01/01/1990 00:00:00");
        }
    }

    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return self::MESSAGE_IDENTITY;
    }

    /**
     * Check whether the message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        // We read info from the saved configuration first, to have values even if GitHub doesn't answer properly
        $this->readFromConf();

        $curdate = new DateTime();

        /*
         * PT1H => Interval of 1 hour
         * https://www.php.net/manual/en/dateinterval.construct.php
         */
        if ($this->lastGithubPoll->add(new \DateInterval("PT1H")) < $curdate) {
            // Request GitHub with Magento's HTTP Client
            $this->curl->setTimeout(3);
            $this->curl->addHeader('User-Agent', 'PHP');

            if ($githubToken = getenv('GITHUB_API_TOKEN')) {
                $this->curl->addHeader('Authorization', 'token ' . $githubToken);
            }

            try {
                $this->curl->get(self::HIPAY_GITHUB_MAGENTO2_LATEST);
                $res = $this->curl->getBody();
            } catch (\Exception $e) {
                $res = '';
            }

            // Decode JSON without error suppression
            $gitHubInfo = null;
            if ($res !== false && $res !== '') {
                $gitHubInfo = json_decode($res);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $gitHubInfo = null;
                }
            }

            // If call is successful, reading from call
            if ($gitHubInfo) {
                $this->newVersion = isset($gitHubInfo->tag_name) ? $gitHubInfo->tag_name : null;
                $this->newVersionDate = isset($gitHubInfo->published_at) ? $gitHubInfo->published_at : null;
                $this->readMeUrl = isset($gitHubInfo->html_url) ? $gitHubInfo->html_url : null;
                $this->lastGithubPoll = $curdate;

                $infoFormatted = new \stdClass();
                $infoFormatted->newVersion = $this->newVersion;
                $infoFormatted->newVersionDate = $this->newVersionDate;
                $infoFormatted->readMeUrl = $this->readMeUrl;
                $infoFormatted->lastCall = $curdate->format(self::DATE_FORMAT);

                $this->_config->setModuleVersionInfo(json_encode($infoFormatted));
            }
        }

        $message = __(
            "We advise you to update the extension if you wish to get the " .
                "latest fixes and evolutions. " .
                "To update the extension, please click here : "
        ) .  $this->readMeUrl;
        $title = __("HiPay Enterprise %1 available", $this->newVersion);
        $versionData[] = [
            'severity' => $this->getSeverity(),
            'date_added' => $this->newVersionDate,
            'title' => $title,
            'description' => $message,
            'url' => $this->readMeUrl,
        ];

        if ($this->version != $this->newVersion
            && !$this->_notifHelper->isNotificationAlreadyAdded($versionData[0])
        ) {
            $this->_inbox->create()->parse(array_reverse($versionData));
        }
        /*
         * This will compare the currently installed version with the latest available one.
         * A message will appear after the login if the two are not matching.
         */
        if ($this->version != $this->newVersion
            && !$this->_notifHelper->isNotificationAlreadyRead($versionData[0])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve system message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        $message = __(
            "We advise you to update the extension if you wish to get the " .
                "latest fixes and evolutions. " .
                "To update the extension, please click here : "
        ) . "<a href='" . $this->readMeUrl . "' target='_blank'>" . $this->readMeUrl . "</a>";
        $title = __("HiPay Enterprise %1 available", $this->newVersion);

        return __('<b>' . $title . '</b><br/>' . $message);
    }

    /**
     * Retrieve system message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_NOTICE;
    }
}
