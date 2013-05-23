<?php

/**
 * Provides bugfixes and enahncements to Paypal IPN handling.
 *
 * @author Luke Mills <luke@aligent.com.au>
 * @author Jim O'Halloran <jim@aligent.com.au>
 */
class Aligent_Paypal_Model_Ipn extends Mage_Paypal_Model_Ipn {
    const CONFIG_IPN_REFUND_METHOD = 'paypal/api/ipn_refund_method';

    /**
     * Process a refund or a chargeback
     */
    protected function _registerPaymentRefund() {
		if (Mage::getStoreConfig(self::CONFIG_IPN_REFUND_METHOD) == Aligent_Paypal_Model_System_Config_Source_Refundmethod::METHOD_DEFAULT) {
            return parent::_registerPaymentRefund();
        } else {

            $this->_importPaymentInformation();
            $reason = $this->getRequestData('reason_code');
            $isRefundFinal = !$this->_info->isReversalDisputable($reason);
            $amount = -1 * $this->getRequestData('mc_gross');

            Mage::log('IPN Refund received.  Reason Code: '.$reason.' isRefundFinal: '.$isRefundFinal.' Amount: '.$amount);


        }

    }

}
