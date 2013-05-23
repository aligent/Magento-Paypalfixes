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

            $vCommentText = Mage::helper('paypal')->__('Refunded amount of %s. Transaction ID: "%s".', $this->_order->getBaseCurrency()->formatTxt($amount), $this->getRequestData('txn_id'));
            $vCommentText .= ' Reason: '.$this->_info->explainReasonCode($reason);

            $this->_createIpnComment($vCommentText, true);
            $this->_order->save();

            $vCommentText .= " for order #".$this->_order->getIncrementId();

            $oNotification = Mage::getModel('adminnotification/inbox');
            $oNotification->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
            $oNotification->setDateAdded(date("c", time()));
            $oNotification->setTitle('Paypal Refund IPN Received');
            $oNotification->setDescription($vCommentText);
            $oNotification->save();

        }

    }

}
