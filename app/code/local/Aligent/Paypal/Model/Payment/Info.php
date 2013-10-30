<?php
/**
 * Created to fix a bug in Magento. For more info
 * refer:http://www.magentocommerce.com/bug-tracking/issue?issue=15975
 * Author:swapna@aligent.com.au
 * Date: 30/10/13
 */
class Aligent_Paypal_Model_Payment_Info extends Mage_Sales_Model_Quote_Payment {

    public function unsAdditionalInformation($key = null)
    {
        if ($key) {
            $info = $this->_getData('additional_information');
            if (is_array($info)) {
                unset($info[$key]);
            }
        } else {
            $info = array();
        }
        return $this->setData('additional_information', $info);
    }
}