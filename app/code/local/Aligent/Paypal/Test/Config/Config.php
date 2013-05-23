<?php

class Aligent_Paypal_Test_Config_Config extends EcomDev_PHPUnit_Test_Case_Config {

    /**
     * Test classes are aliased correctly
     * 
     * @test
     */
    public function testClassAliases(){
        $this->assertModelAlias('aligent_paypal/ipn', 'Aligent_Paypal_Model_Ipn');
        $this->assertModelAlias('paypal/ipn', 'Aligent_Paypal_Model_Ipn');
    }
    
}