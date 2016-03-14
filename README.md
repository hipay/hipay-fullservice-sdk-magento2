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

#### HOSTED METHOD

##### Method class

You need to create a new PHP class in `src/Model/Method` directory.  
This class must inherit [CcMethod](src/Model/CcMethod.php) or [HostedMethod](src/Model/HostedMethod.php).
The minimum required it's to enter a code for this method.

But if you need to customize payment feature like 3ds, you need to override protected variables or public method;
All payment feature are initialy declared in abstract call [FullserviceMethod](src/Model/FullserviceMethod.php).

See example below with *Sisal* in `HOSTED` mode:  


```php
// Sisal.php in Model/Method folder.

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
	protected $_canRefund = false;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefundInvoicePartial = false;
	
	
	
}
```

##### Method configuration 

In Magento2 you can import content of a file configuration in an another file.
This done with `include` XML tag in configuration file.  
You can see in [src/etc/adminhtml/system](src/etc/adminhtml/system) directory the list of segmented configurations files.


So now, create your payment method configuration file and save it in `src/etc/adminhtml/system`.  
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
				<include path="HiPay_FullserviceMagento::system/method/base_top.xml"/>
                
                 <!-- custom fields or override of hosted/Cc -->
                 <field id="css_url" translate="label comment" type="text" sortOrder="80" showInDefault="1" showInWebsite="1" showInStore="0">
                    <label>Custom CSS url</label>
                    <comment>Important, HTTPS protocol is required</comment>
                </field> 
                 <field id="template" translate="label" type="select" sortOrder="90" showInDefault="1" showInWebsite="1" showInStore="0">
                    <label>Template type</label>
                    <source_model>HiPay\FullserviceMagento\Model\System\Config\Source\Templates</source_model>
                </field>
                <field id="iframe_mode" translate="label" type="select" sortOrder="100" showInDefault="1" showInWebsite="1" showInStore="0">
                    <label>Display hosted page in Iframe</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                <!-- 
					Include tag import configuration from another file.
					base_bottom.xml contain configuration fields will be appear on bottom
					like test mode, debug, sort order etc ..
				-->
                <include path="HiPay_FullserviceMagento::system/method/base_bottom.xml"/>
                
			</group>
</include>
```
 
And include this configuration file in payment section of [system.xml](src/etc/adminhtml/system.xml):

```xml
<section id="payment">
	<include path="HiPay_FullserviceMagento::system/method/hosted.xml"/>
	<include path="HiPay_FullserviceMagento::system/method/cc.xml"/>
	<!-- Sisal file configuration -->
	<include path="HiPay_FullserviceMagento::system/method/sisal.xml"/>		
</section>
``` 

For gloab declaration of your payment method, you should add a node in [payment.xml](src/etc/payment.xml):

```xml
<payment xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Payment:etc/payment.xsd">
    <methods>
        <method name="hipay_hosted">
            <allow_multiple_address>0</allow_multiple_address>
        </method>
        <method name="hipay_cc">
            <allow_multiple_address>0</allow_multiple_address>
        </method>
        <!-- Local method -->
        <method name="hipay_sisal">
            <allow_multiple_address>0</allow_multiple_address>
        </method>
    </methods>
</payment>
```

Finally, enter method's default configuration node in [config.xml](src/etc/config.xml):


```xml
<hipay_sisal>
	<model>HiPay\FullserviceMagento\Model\Method\Sisal</model>
	<payment_method>HiPay\FullserviceMagento\Model\Request\PaymentMethod\CardToken</payment_method>
    <active>0</active>
    <title>Sisal</title>
    <payment_action>Sale</payment_action>
    <order_status>pending</order_status>
    <iframe_mode>1</iframe_mode>
    <payment_products>sisal</payment_products> <!-- Enter payment code value see payment products collection in SDK PHP -->
    <payment_products_categories>realtime-banking</payment_products_categories>
    <display_selector>0</display_selector>
    <authentication_indicator>0</authentication_indicator> <!-- Enable/Disable 3D secure -->
    <template>basic-js</template>
    <css></css>
    <allowspecific>0</allowspecific>
    <max_order_total>1000</max_order_total> <!-- Custom local configuration -->
    <allowed_currencies>EUR</allowed_currencies> <!-- Custom local configuration -->
    <debug>0</debug>
    <env>STAGE</env>
</hipay_sisal>
```

You need to report the method model created previously in `model` tag.  
Payment method class is used to add the map specifique payment method request attributes.  

The `payment_products` tag is used to display payment methods in payment form.  
In our *Sisal* example, we limit to `sisal` product code.  
`display_selector` is alose disabled with zero value.  

If the local method need some custom configurations, you can report it here.  
For example, Sisal do not allow a transaction more than 1000 euro.  
So we have enter `max_order_total` and `allowed_currencies` tags with custom data.

##### Add javascript client template

Magento2 provide a javascript templating engine based on knockout.js.
To display the local payment method for this type of template, you need to declare you payment method in render list in [hipay-methods.js](src/view/frontend/web/js/view/payment/hipay-methods.js).

```javascript
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'hipay_hosted',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-hosted'
            },
            {
                type: 'hipay_cc',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-cc'
            },
            {
            	// New local method with hosted template
                type: 'hipay_sisal',
                component: 'HiPay_FullserviceMagento/js/view/payment/method-renderer/hipay-sisal'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
```


In your declaration, you must to enter a component name, you can override hipay-hosted like [hipay-sisal.js](src/view/frontend/web/js/view/payment/method-renderer/hipay-sisal.js):

```javascript

define(
    [
        'HiPay_fullserviceMagentp/js/view/payment/method-renderer/hipay-hosted', //@override hipay-hosted
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-hosted', //template file
                redirectAfterPlaceOrder: false
            },
	        getCode: function() {
	            return 'hipay_sisal'; /:Declare your method code
	        },
            isActive: function() {
                return true;
            }
        });
    }
);
```

If you want to create your custom template, put its name in default value template and create your file in `src/view/frontend/web/template/payment/` .

Finally, enter a node in [checkout_index_index.xml](src/view/frontend/layout/checkout_index_index.xml) to merge your method render.

```xml
...
<!-- merge payment method renders here -->
<item name="children" xsi:type="array">
    <item name="hipay-payments" xsi:type="array">
        <item name="component" xsi:type="string">HiPay_FullserviceMagento/js/view/payment/hipay-methods</item>
        <item name="methods" xsi:type="array">
            <item name="hipay_hosted" xsi:type="array">
                <item name="isBillingAddressRequired" xsi:type="boolean">true</item>
            </item>
           <item name="hipay_cc" xsi:type="array">
                <item name="isBillingAddressRequired" xsi:type="boolean">true</item>
            </item>
            <!-- Local method -->
            <item name="hipay_sisal" xsi:type="array">
                <item name="isBillingAddressRequired" xsi:type="boolean">true</item>
            </item>
        </item>
    </item>
</item>
...
```


#### API METHOD

API METHOD is used to **request a new order** to TPP Fullservice API.
As the same way CcMethod, you can provide form field on checkout.

Examples are based on Qiwi Wallet payment method.

##### Method Class

Follow the same steps that HOSTED METHOD but override directly [FullserviceMethod.php](src/Model/FullserviceMethod.php).

See example below with *Qiwi Wallet* in `API` mode:  

```php
namespace Hipay\FullserviceMagento\Model\Method;

use HiPay\FullserviceMagento\Model\FullserviceMethod;

class QiwiWallet extends FullserviceMethod{
	
	const HIPAY_METHOD_CODE               = 'hipay_qiwiwallet';
	
	/**
	 * @var string
	 */
	protected $_code = self::HIPAY_METHOD_CODE;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefund = false;
	
	/**
	 * Payment Method feature
	 *
	 * @var bool
	 */
	protected $_canRefundInvoicePartial = false;
	
	/**
	 * @var string[] keys to import in payment additionnal informations
	 */
	protected $_additionalInformationKeys = ['qiwiuser'];
	
	
	
}
```

The import point here is `$_additionalInformationKeys` property. All contained keys are automatically assigned to payment additional information.  

#####  Method configuration 

Do the same that HOSTED METHOD.  Just remove not used tags in system.xml (qiwi-wallet.xml here) and config.xml.

Set `model` and `payment_method` values in config.xml .

```xml
<hipay_qiwiwallet>
	<model>HiPay\FullserviceMagento\Model\Method\QiwiWallet</model>
	<payment_method>HiPay\FullserviceMagento\Model\Request\PaymentMethod\QiwiWallet</payment_method>
    <active>0</active>
    <title>Qiwi wallet</title>
    <payment_action>Sale</payment_action>
    <order_status>pending</order_status>
    <payment_products>qiwi-wallet</payment_products> <!-- Enter payment code value see payment products collection in SDK PHP -->
    <payment_products_categories>ewallet</payment_products_categories>
    <authentication_indicator>0</authentication_indicator> <!-- Enable/Disable 3D secure -->
    <allowspecific>0</allowspecific>
    <allowed_currencies>RUB</allowed_currencies> <!-- Custom local configuration -->
    <debug>0</debug>
    <env>STAGE</env>
</hipay_qiwiwallet>
```

Change `id` in qiwi-wallet.xml.  In this case, we no need custom fields:  

```xml
<?xml version="1.0" encoding="UTF-8"?>
<include xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_include.xsd">
<group id="hipay_qiwiwallet" translate="label" type="text" sortOrder="6" showInDefault="1" showInWebsite="1" showInStore="1">
				<label>HiPay Fullservice Qiwi Wallet API</label>
				<comment></comment>
				<!-- 
					Include tag import configuration from another file.
					base_top.xml contain configuration fields will be appear on top
					like enabled, title, order statues etc ...
				-->
				<include path="HiPay_FullserviceMagento::system/method/base_top.xml"/>
                
                 <!-- no custom fields needed -->
                
                
                <!-- 
					Include tag import configuration from another file.
					base_bottom.xml contain configuration fields will be appear on bottom
					like test mode, debug, sort order etc ..
				-->
                <include path="HiPay_FullserviceMagento::system/method/base_bottom.xml"/>
                
			</group>
</include>
```

Don't forget to add your method in [payment.xml](src/etc/payment.xml) and include [qiwi-wallet.xml](src/etc/adminhtml/system/method/qiwi-wallet.xml) file in [system.xml](src/etc/adminhtml/system.xml).


##### Add javascript client template

It's globaly the same method as for HOSTED METHOD but you have to code for validation form and data to send in renderer javascript file.  
And create yout html template to add your form fields.   


[hipay-qiwiwallet.js](src/view/frontend/web/js/view/payment/method-renderer/hipay-qiwiwallet.js)  
```javascript
define(
    [
     	'jquery',
        'Magento_Checkout/js/view/payment/default',
    ],
    function ($, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'HiPay_FullserviceMagento/payment/hipay-qiwiwallet',
                qiwiUserId: '',
                redirectAfterPlaceOrder: false,
                afterPlaceOrderUrl: window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl,
                paymentForm: $("co-qiwiwallet-form")
            },
            /**
             * Handler used by transparent
             */
            placeOrderHandler: null,
            validateHandler: null,
            
            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Function} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },
            initialize: function() {
                var self = this;
                this._super();

            },
            initObservable: function () {
                this._super()
                    .observe([
                        'qiwiUserId',
                    ]);
                
                this.paymentForm.validation();
                return this;
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'qiwiuser': this.qiwiUserId(),
                    }
                };
            },
	        getCode: function() {
	            return 'hipay_qiwiwallet';
	        },
	        /**
	         * Needed by transparent.js
	         */
	        context: function() {
                return this;
            },
            isActive: function() {
                return true;
            },
            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },
            validate: function(){
            	return (this.paymentForm.validation && this.paymentForm.validation('isValid'));

            },
            /**
             * After place order callback
             */
	        afterPlaceOrder: function () {
	        	 $.mage.redirect(this.afterPlaceOrderUrl);
	        },
        });
    }
);
```

Part of [hipay-qiwiwallet.html](src/view/frontend/web/template/payment/hipay-qiwiwallet.html)  
```html
...
 <form class="form" id="co-qiwiwallet-form" action="#" method="post" data-bind="mageInit: {'validation':[]}">
		
			<fieldset data-bind="attr: {class: 'fieldset payment items ' + getCode(), id: 'payment_form_' + getCode()}">
			    <!-- ko if: (isShowLegend())-->
			    <legend class="legend">
			        <span><!-- ko i18n: 'Qiwi Wallet Information'--><!-- /ko --></span>
			    </legend><br />
			    <!-- /ko -->
			    <div class="field number required">
			        <label data-bind="attr: {for: getCode() + '_qiwiuser'}" class="label">
			            <span><!-- ko i18n: 'Qiwi User Phone Number'--><!-- /ko --></span>
			        </label>
			        <div class="control">
			            <input type="text" name="payment[qiwiuser]" class="input-text" value=""
			                   data-bind="attr: {
			                                    autocomplete: off,
			                                    id: getCode() + '_qiwiuser',
			                                    title: $t('Qiwi User Phone Number'),
			                                    'data-container': getCode() + '-qiwiuser',
			                                    'data-validate': JSON.stringify({'required-text':true, 'no-whitespace':'#' + getCode() + '_qiwiuser'})},
			                              enable: isActive($parents),
			                              value: qiwiUserId,
			                              valueUpdate: 'keyup' "/>
			        </div>
			    </div>
			</fieldset>
	</form>
...
```

Finally, enter a node in [checkout_index_index.xml](src/view/frontend/layout/checkout_index_index.xml) to merge your method render.

