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
