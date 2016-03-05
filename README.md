# HiPay Fullservice Module Magento2


### Payment Notifications

During payment workflow, order status is updated only with HiPay fullservice notifications.   
The endpoint of notification is `http://yourawesomewebsite.com/hipay/notify/index`.  
It is protected by a passphrase encrypted. So don't forget to enter it in your Magento and HiPay BO.  

For more informations, you can see the treatment in Model [Notify](src/Model/Notify.php).

### Transaction statues

All HiPay fullservice transactions statues are processed but not all them interact with Magento order statues.  
This treatment occure only when notification is received (see. Notification part).  

When a statut is processing we create a magento payment transaction.  
Else we just add a new order history record with notifications informations.  

HiPay fullservice statues interaction:

- *BLOCKED* (`110`) and *DENIED* (`111`)  
    Transaction type **"Denied"** is created:
    - Transaction is closed
    - If invoice in status *pending* exists, it's canceled
    - The order is cancelled too


- *AUTHORIZED AND PENDING* (`112`) and *PENDING PAYMENT* (`200`)  
    Transaction type **"Authorization"** is created:
    - Transaction is in *pending*
    - Transaction isn't closed
    - Order status change to `Pending Review`
    - Invoice isn't created


- *REFUSED* (`113`), *CANCELLED* (`115`), *AUTHORIZATION_REFUSED* (`163`), *CAPTURE_REFUSED* (`163`)  
     Transaction is *not created*:  
     - The order is `cancelled`
     - If invoice exists, it's canceled too
     

- *EXPIRED* (`114`)  
  Transaction type **"Void"** is created if parent transaction authorization:  
    - @TODO define the process
    

- *AUTHORIZED* (`116`)  
  Transaction type **"Authorization"** is created:  
  - Transaction is open
  - Order status change to `Authorized`
  - Invoice isn't created
  

- *CAPTURE REQUESTED* (`117`)  
  Transaction is not created:  
  - Order status change to `Capture requested`
  - Notification details are added to order history
  

- *CAPTURED* (`118`) and **PARTIALLY CAPTURED** (`119`)  
  Transaction type **"Capture"** is created:  
  - Transaction still open
  - Order status change to `Processing` or `Partially captured`
  - Invoice complete/partial is created
  

- *REFUND_REQUESTED* (`124`)  
  Transaction is not created:  
  - Order status change to `Refund requested`
  - Notification details are added to order history
  

- *REFUNDED* (`125`) and **PARTIALLY REFUNDED** (`126`)  
  Transaction type **"Capture"** is created:  
  - Transaction still open
  - Order status change to `Processing` or `Partially captured`
  - Invoice complete/partial is created
  

- *REFUND REFUSED* (`117`)  
  Transaction is not created:  
  - Order status change to `Refund refused`
  - Notification details are added to order history
  

- *CREATED* (`101`)
- *CARD HOLDER ENROLLED* (`103`)
- *CARD HOLDER NOT ENROLLED* (`104`)
- *UNABLE TO AUTHENTICATE* (`105`)
- *CARD HOLDER AUTHENTICATED* (`106`)
- *AUTHENTICATION ATTEMPTED* (`107`)
- *COULD NOT AUTHENTICATE* (`108`)
- *AUTHENTICATION FAILED* (`109`)
- *COLLECTED* (`120`)
- *PARTIALLY COLLECTED* (`121`)
- *SETTLED* (`122`)
- *PARTIALLY SETTLED* (`123`)
- *CHARGED BACK* (`129`)
- *DEBITED* (`131`)
- *PARTIALLY DEBITED* (`132`)
- *AUTHENTICATION REQUESTED* (`140`)
- *AUTHENTICATED* (`141`)
- *AUTHORIZATION REQUESTED* (`142`)
- *ACQUIRER FOUND* (`150`)
- *ACQUIRER NOT FOUND* (`151`)
- *CARD HOLDER ENROLLMENT UNKNOWN* (`160`)
- *RISK ACCEPTED* (`161`)  
    Transaction is not created:  
  - Orde status *don't change*
  - Notification details are added to order history
  
  
### Local payment easy managment

You simlply add a new local payment method based on Fullservice `API` or `HOSTED`.  
Steps below explain how to add it:


1.  Method class

You need to create a new PHP class in `src/Model/Method` directory.  
This class must inherit [CcMethod](src/Model/CcMethod.php) or [HostedMethod](src/Model/HostedMethod.php).
The minimum required it's to enter a code for this method.

But if you need to customize payment feature like 3ds, you need to override protected variables or public method;
All payment feature are initialy declared in abstract call [FullserviceMethod](src/Model/FullserviceMethod.php).

See example below with *Sisal* in `HOSTED` mode:  


```php
# Sisal.php in Model/Method folder.

namespace Hipay\FullserviceMagento\Model\Method;

use HiPay\FullserviceMagento\Model\HostedMethod;

class Sisal extends HostedMethod{
	
	const HIPAY_METHOD_CODE               = 'hipay_sisal';
	
	/**
	 * @var string
	 */
	protected $_code = self::HIPAY_METHOD_CODE;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefund = true;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefundInvoicePartial = true;
	
	
	
}
```

2.  Method configuration 

In Magento2 you can import content of a file configuration in an another file.
This done with `include` XML tag in configuration file.  
You can see in [src/etc/adminhtml/system](src/etc/adminhtml/system) directory the list of segmented configurations files.


So now, create your payment method configuration file and save it in src/etc/adminhtml/system`.
Don't forget to enter your method code in `id` attribute of `groups` tag and to change the `label` tag.

Example for Sisal method with a minimum configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<include xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_include.xsd">
<group id="hipay_sisal" translate="label" type="text" sortOrder="5" showInDefault="1" showInWebsite="1" showInStore="1">
				<label>HiPay Fullservice SISAL Hosted</label>
				<comment></comment>
				<!-- 
					Include tag import configuration from another file.
					base_top.xml contain configuration fields will be appear on top
					like enabled, title, order statues etc ...
				-->
				<include path="HiPay_FullserviceMagento::system/base_top.xml"/>
                
                 <!-- custom fields or override of hosted/Cc -->
                 <field id="css_url" translate="label comment" type="text" sortOrder="80" showInDefault="1" showInWebsite="1" showInStore="0">
                    <label>Custom CSS url</label>
                    <comment>Important, HTTPS protocol is required</comment>
                </field> 
                 <field id="template" translate="label" type="select" sortOrder="90" showInDefault="1" showInWebsite="1" showInStore="0">
                    <label>Template type</label>
                    <source_model>HiPay\FullserviceMagento\Model\System\Config\Source\Templates</source_model>
                </field>
                
                <!-- 
					Include tag import configuration from another file.
					base_bottom.xml contain configuration fields will be appear on bottom
					like test mode, debug, sort order etc ..
				-->
                <include path="HiPay_FullserviceMagento::system/base_bottom.xml"/>
                
			</group>
</include>
```
 
And include this configuration file in payment section of [system.xml](src/etc/adminhtml/system.xml):

```xml
<section id="payment">
	<include path="HiPay_FullserviceMagento::system/hosted.xml"/>
	<include path="HiPay_FullserviceMagento::system/cc.xml"/>
	<!-- Sisal file configuration -->
	<include path="HiPay_FullserviceMagento::system/sisal.xml"/>			
</section>
``` 


Finally, enter method's default configuration node in [config.xml](src/etc/config.xml):


```xml
<hipay_sisal>
	<model>HiPay\FullserviceMagento\Model\Method\Sisal</model>
    <active>0</active>
    <title>Sisal</title>
    <payment_action>Sale</payment_action>
    <order_status>pending</order_status>
    <display_selector>0</display_selector>
    <authentication_indicator>0</authentication_indicator>
    <template>basic-js</template>
    <css></css>
    <allowspecific>0</allowspecific>
    <debug>0</debug>
    <env>STAGE</env>
</hipay_sisal>
```

You need to report the method model created previously in `model` tag.

