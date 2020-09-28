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
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */

namespace HiPay\FullserviceMagento\Block\Adminhtml\System\Config;

/**
/**
 * Update notification block
 *
 * @package HiPay\FullserviceMagento\Block\Adminhtml\System\Config
 * @author Hipay
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class UpdateNotif implements \Magento\Framework\Notification\MessageInterface
{
    const HIPAY_GITHUB_MAGENTO2_LATEST = "https://api.github.com/repos/hipay/hipay-fullservice-sdk-magento2/releases/latest";

    /**
     * Message identity
     */
    const MESSAGE_IDENTITY = 'HipPay Version Notification';

    protected $_authSession;

    /**
     * @var \HiPay\FullserviceMagento\Helper\Data $_helper
     */
    protected $_helper;

    /**
     * @var \HiPay\FullserviceMagento\Helper\Notification $_notifHelper
     */
    protected $_notifHelper;

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory $_inbox
     */
    protected $_inbox;

    /**
     * @var \HiPay\FullserviceMagento\Model\Config
     */
    protected $_config;

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

    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \HiPay\FullserviceMagento\Helper\Data $hipayHelper,
        \HiPay\FullserviceMagento\Helper\Notification $hipayNotificationHelper,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \HiPay\FullserviceMagento\Model\Config\Factory $configFactory
    )
    {
        $this->_authSession = $authSession;
        $this->_helper = $hipayHelper;
        $this->_notifHelper = $hipayNotificationHelper;
        $this->_inbox = $inboxFactory;
        $this->_config = $configFactory->create();
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
            $this->lastGithubPoll = \DateTime::createFromFormat('d/m/Y H:i:s', $lastResult->lastCall);

            // If not, setting default data with values not showing the block
        } else {
            $this->newVersion = $this->version;
            $this->newVersionDate = \DateTime::createFromFormat('d/m/Y H:i:s', "01/01/1990 00:00:00");
            $this->readMeUrl = "#";
            $this->lastGithubPoll = \DateTime::createFromFormat('d/m/Y H:i:s', "01/01/1990 00:00:00");
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

        $curdate = new \DateTime();

        /*
         * PT1H => Interval of 1 hour
         * https://www.php.net/manual/en/dateinterval.construct.php
         */
        if ($this->lastGithubPoll->add(new \DateInterval("PT1H")) < $curdate) {
            // Request GitHub with curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::HIPAY_GITHUB_MAGENTO2_LATEST);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
            $res = curl_exec($ch);
            curl_close($ch);
            $gitHubInfo = @json_decode($res);
            
            // If call is successful, reading from call
            if ($gitHubInfo) {
                $this->newVersion = $gitHubInfo->tag_name;
                $this->newVersionDate = $gitHubInfo->published_at;
                $this->readMeUrl = $gitHubInfo->html_url;
                $this->lastGithubPoll = $curdate;

                $infoFormatted = new \stdClass();
                $infoFormatted->newVersion = $this->newVersion;
                $infoFormatted->newVersionDate = $this->newVersionDate;
                $infoFormatted->readMeUrl = $this->readMeUrl;
                $infoFormatted->lastCall = $curdate->format('d/m/Y H:i:s');

                $this->_config->setModuleVersionInfo(json_encode($infoFormatted));
            }
        }
        try {

            $message = __("We advise you to update the extension if you wish to get the " .
                "latest fixes and evolutions. " .
                "To update the extension, please click here : ") .  $this->readMeUrl;
            $title = __("HiPay Enterprise %1 available", $this->newVersion);
            $versionData[] = array(
                'severity' => $this->getSeverity(),
                'date_added' => $this->newVersionDate,
                'title' => $title,
                'description' => $message,
                'url' => $this->readMeUrl,
            );

            if($this->version != $this->newVersion && !$this->_notifHelper->isNotificationAlreadyAdded($versionData[0])){
                $this->_inbox->create()->parse(array_reverse($versionData));
            }
            /*
             * This will compare the currently installed version with the latest available one.
             * A message will appear after the login if the two are not matching.
             */
            if ($this->version != $this->newVersion && !$this->_notifHelper->isNotificationAlreadyRead($versionData[0])) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
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
        $message = __("We advise you to update the extension if you wish to get the " .
            "latest fixes and evolutions. " .
            "To update the extension, please click here : ") . "<a href='" . $this->readMeUrl . "' target='_blank'>" . $this->readMeUrl . "</a>";
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
