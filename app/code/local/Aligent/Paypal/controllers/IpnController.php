<?php

require_once 'Mage/Paypal/controllers/IpnController.php';


/**
 * Unified IPN controller for all supported PayPal methods
 */
class Aligent_Paypal_IpnController extends Mage_Paypal_IpnController {


    /**
     * Instantiate IPN model and pass IPN request to it
     */
    public function indexAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        try {
            $data = $this->getRequest()->getPost();
            Mage::getModel('paypal/ipn')->processIpnRequest($data, new Varien_Http_Adapter_Curl());
        } catch (Exception $e) {
            if (function_exists('newrelic_notice_error')) {

                /**
                 * Adds error to New Relic
                 * @link https://newrelic.com/docs/php/the-php-api#api-notice-error
                 */
                newrelic_notice_error($e->getMessage(), $e);
            }

            Mage::logException($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}
