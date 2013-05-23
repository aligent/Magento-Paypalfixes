<?php

/**
 * Provides bugfixes and enahncements to Paypal IPN handling.
 *
 * @author Luke Mills <luke@aligent.com.au>
 * @author Jim O'Halloran <jim@aligent.com.au>
 */
class Aligent_Paypal_Model_Ipn extends Mage_Paypal_Model_Ipn {

    /**
     * IPN workflow implementation
     * Everything should be added to order comments. In positive processing cases customer will get email notifications.
     * Admin will be notified on errors.
     */
    protected function _processOrder()
    {
        $this->_order = null;
        $this->_getOrder();

        $this->_info = Mage::getSingleton('paypal/info');
        try {
            // handle payment_status
            $paymentStatus = $this->_filterPaymentStatus($this->_request['payment_status']);

            switch ($paymentStatus) {
                // paid
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED:
                    $this->_registerPaymentCapture();
                    break;

                // the holded payment was denied on paypal side
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_DENIED:
                    $this->_registerPaymentDenial();
                    break;

                // customer attempted to pay via bank account, but failed
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_FAILED:
                    // cancel order
                    $this->_registerPaymentFailure();
                    break;

                // refund forced by PayPal
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_REVERSED: // break is intentionally omitted
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_UNREVERSED: // or returned back :)
                    $this->_registerPaymentReversal();
                    break;

                // refund by merchant on PayPal side
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDED:
					Mage::log('IPN STATUS PAYMENTSTATUS_REFUNDED', null, 'mylogfile.log');
                    $this->_registerPaymentRefund();
                    break;

                // payment was obtained, but money were not captured yet
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING:
                    $this->_registerPaymentPending();
                    break;

                // MassPayments success
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_PROCESSED:
                    $this->_registerMasspaymentsSuccess();
                    break;

                // authorization expire/void
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_EXPIRED: // break is intentionally omitted
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_VOIDED:
                    $this->_registerPaymentVoid();
                    break;

                default:
                    throw new Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
        } catch (Mage_Core_Exception $e) {
            $comment = $this->_createIpnComment(Mage::helper('paypal')->__('Note: %s', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }



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
