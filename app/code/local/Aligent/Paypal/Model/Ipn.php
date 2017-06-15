<?php

/**
 * Provides bugfixes and enhancements to Paypal IPN handling.
 *
 * @author Luke Mills <luke@aligent.com.au>
 * @author Jim O'Halloran <jim@aligent.com.au>
 */
class Aligent_Paypal_Model_Ipn extends Mage_Paypal_Model_Ipn
{
    const CONFIG_IPN_REFUND_METHOD = 'payment/modpaypal/ipn_refund_method';

    /**
     * Default postback endpoint URL.
     *
     * @var string
     */
    const DEFAULT_POSTBACK_URL = 'https://ipnpb.paypal.com/cgi-bin/webscr';

    /**
     * Sandbox postback endpoint URL.
     *
     * @var string
     */
    const SANDBOX_POSTBACK_URL = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * Get ipn data, send verification to PayPal, run corresponding handler.  Override
     * to allow handling of mc_cancel transactions which don't have an order increment
     * id attached.
     *
     * @param array $request
     * @param Zend_Http_Client_Adapter_Interface $httpAdapter
     * @throws Exception
     */
    public function processIpnRequest(array $request, Zend_Http_Client_Adapter_Interface $httpAdapter = null)
    {
        $this->_request   = $request;
        $this->_debugData = array('ipn' => $request);
        ksort($this->_debugData['ipn']);

        if (isset($this->_request['reason_code']) && 'refund' == $this->_request['reason_code']) {
            $this->_registerMpCancel();
        } else {
            parent::processIpnRequest($request, $httpAdapter);
        }
    }


    /**
     * Process a cancellation.  Standard Magento fails at this because there's
     * no order number in the IPN.
     */
    protected function _registerMpCancel()
    {
        $reason = $this->getRequestData('reason_code');

        Mage::log('IPN Cancellation received.  Reason Code: '.$reason.' Payer Email: ' . $this->getRequestData('payer_email') . ' First Name: ' . $this->getRequestData('payer_first_name') . ' Last Name: ' . $this->getRequestData('last_name'));

        $vCommentText = 'Payer Email: ' . $this->getRequestData('payer_email') . ' First Name: ' . $this->getRequestData('payer_first_name');
        $vCommentText .= ' Last Name: ' . $this->getRequestData('last_name');
        $vCommentText .= ' Reason Code: ' . $reason;

        $oNotification = Mage::getModel('adminnotification/inbox');
        $oNotification->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
        $oNotification->setDateAdded(date("c", time()));
        $oNotification->setTitle('Paypal Cancellation IPN Received');
        $oNotification->setDescription($vCommentText);
        $oNotification->save();
    }


    /**
     * Process a refund or a chargeback
     */
    protected function _registerPaymentRefund()
    {
        if (Mage::getStoreConfig(self::CONFIG_IPN_REFUND_METHOD) == Aligent_Paypal_Model_System_Config_Source_Refundmethod::METHOD_DEFAULT) {
            return parent::_registerPaymentRefund();
        } else {

            $this->_importPaymentInformation();
            $reason = $this->getRequestData('reason_code');
            $isRefundFinal = !$this->_info->isReversalDisputable($reason);
            $amount = -1 * $this->getRequestData('mc_gross');

            Mage::log('IPN Refund received.  Reason Code: ' . $reason . ' isRefundFinal: ' . $isRefundFinal . ' Amount: ' . $amount);

            $vCommentText = Mage::helper('paypal')->__('Refunded amount of %s. Transaction ID: "%s".', $this->_order->getBaseCurrency()->formatTxt($amount), $this->getRequestData('txn_id'));
            $vCommentText .= ' Reason: ' . $this->_info->explainReasonCode($reason);

            $this->_createIpnComment($vCommentText, true);
            $this->_order->save();

            $vCommentText .= " for order #" . $this->_order->getIncrementId();

            $oNotification = Mage::getModel('adminnotification/inbox');
            $oNotification->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
            $oNotification->setDateAdded(date("c", time()));
            $oNotification->setTitle('Paypal Refund IPN Received');
            $oNotification->setDescription($vCommentText);
            $oNotification->save();

        }

    }

    /**
     * Post back to PayPal to check whether this request is a valid one
     *
     * @param  Zend_Http_Client_Adapter_Interface $httpAdapter
     * @throws Exception
     */
    protected function _postBack(Zend_Http_Client_Adapter_Interface $httpAdapter)
    {
        $url = $this->_getPostbackUrl();

        $sReq = '';
        foreach ($this->_request as $k => $v) {
            $sReq .= '&' . $k . '=' . urlencode(stripslashes($v));
        }
        $sReq .= "&cmd=_notify-validate";
        $sReq = substr($sReq, 1);
        $this->_debugData['postback'] = $sReq;
        $this->_debugData['postback_to'] = $url;
        $httpAdapter->addOption(CURLOPT_SSLVERSION,6); //6 == CURL_SSLVERSION_TLSv1_2
        $httpAdapter->write(Zend_Http_Client::POST, $url, '1.1', array(), $sReq);
        try {
            $response = $httpAdapter->read();
        } catch (Exception $e) {
            $this->_debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            throw $e;
        }
        $this->_debugData['postback_result'] = $response;

        // =====================================================================
        // Changed from default code.  Paypal now regularly returns a 100
        // response with an empty body followed by a 200 response with the
        // VERIFIED/INVALID message.  The code below will check the last
        // response for the VERIFIED/INVALID code rather than the first.
        //
        // ref: http://www.dhmedia.com.au/blog/debugging-paypal-ipn-postback-failure-magent
        // Magento 2 Pull Request: https://github.com/magento/magento2/pull/136
        $response = preg_split('/^\r?$/m', $response);
        $response = trim(end($response));
        // =====================================================================

        if ($response != 'VERIFIED') {
            throw new Exception('PayPal IPN postback failure. See ' . self::DEFAULT_LOG_FILE . ' for details.');
        }
        unset($this->_debugData['postback'], $this->_debugData['postback_result']);
    }

    /**
     * Get postback endpoint URL.
     *
     * @return string
     */
    protected function _getPostbackUrl()
    {
        return $this->_config->sandboxFlag ? self::SANDBOX_POSTBACK_URL : self::DEFAULT_POSTBACK_URL;
    }
}
