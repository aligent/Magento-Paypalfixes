Magento Paypal fixes
====================

1) When doing a partial refund at Paypal all items on the order  gets refunded. This module will disable the IPN callback when doing refunds allowing the Magento administrator to manually refund the right product(s).  Administrators will be notified via an admin alert when a PayPal Redund IPN has been received.

2) This module also contains a fix for the "PayPal IPN postback failure" exception which occurs when PayPal uses a HTTP 1.1 response for the postback verification.  See [this Doghousemedia blog post](http://dhmedia.com.au/article/debugging-paypal-ipn-postback-failures-magento) for details.

3) The Payment additional_information array  has been flushed out in the process of unsetting the key 'paypal_express_checkout_shipping_overriden' in the array. This is caused by the bug in Magento, which I have raised http://www.magentocommerce.com/bug-tracking/issue?issue=15975. This
   module fixes the issue by rewriting the class Mage_Sales_Quote_Payment and overriding the function,unsAdditionalInformation($key). This function is inherited from  Mage_Sales_Payment_Info by Mage_Sales_Quote_Payment.