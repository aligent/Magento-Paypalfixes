Magento Paypal fixes
====================

When doing a partial refund at Paypal all items on the order  gets refunded. This module will disable the IPN callback when doing refunds allowing the Magento administrator to manually refund the right product(s).  Administrators will be notified via an admin alert when a PayPal Redund IPN has been received.

This module also contains a fix for the "PayPal IPN postback failure" exception which occurs when PayPal uses a HTTP 1.1 response for the postback verification.  See [this Doghousemedia blog post](http://dhmedia.com.au/article/debugging-paypal-ipn-postback-failures-magento) for details.
