<?php

/**
 * Refund method source
 *
 */
class Aligent_Paypal_Model_System_Config_Source_Refundmethod {
    
    const METHOD_DEFAULT = 'default';
    const METHOD_COMMENT = 'comment';
    
    public function toOptionArray() {
        return array(
            array('value' => self::METHOD_DEFAULT,  'label' => 'Default - Create Credit Memo'),
            array('value' => self::METHOD_COMMENT, 'label' => 'Order Comment Only')
        );
    }
}
