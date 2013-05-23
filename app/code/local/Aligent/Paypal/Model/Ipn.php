<?php

/**
 * Provides bugfixes and enahncements to Paypal IPN handling.
 *
 * @author Luke Mills <luke@aligent.com.au>
 * @author Jim O'Halloran <jim@aligent.com.au>
 */
class Aligent_Paypal_Model_Ipn extends Mage_Paypal_Model_Ipn {

    /**
     * Process a refund or a chargeback
     */
    protected function _registerPaymentRefund() {
		
		$this->_importPaymentInformation();
        $reason = $this->getRequestData('reason_code');
        $isRefundFinal = !$this->_info->isReversalDisputable($reason);
        $amount = -1 * $this->getRequestData('mc_gross');
		
		Mage::log('IPN _registerPaymentRefund()', null, 'mylogfile.log');
		Mage::log('IPN reason_code'.$reason, null, 'mylogfile.log');
		Mage::log('IPN isRefundFinal'.$isRefundFinal, null, 'mylogfile.log');
		Mage::log('IPN amount'.$amount, null, 'mylogfile.log');
       /*

        $payment = $this->_order->getPayment()
                ->setPreparedMessage($this->_createIpnComment($this->_info->explainReasonCode($reason)))
                ->setTransactionId($this->getRequestData('txn_id'))
                ->setParentTransactionId($this->getRequestData('parent_txn_id'))
                ->setIsTransactionClosed($isRefundFinal);
        $this->_order->save();

        $comment = $this->_order->addStatusHistoryComment(
                        Mage::helper('paypal')->__('IPN "Refunded". Refunded amount of %s. Transaction ID: "%s". No automatic credit memo generated.', $this->_order->getBaseCurrentcy()->formatTxt($amount), $this->getRequestData('txn_id'))
                )
                ->save();*/
    }

}
