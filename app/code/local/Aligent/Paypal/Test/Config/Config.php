<?php

class Aligent_Paypal_Test_Config_Config extends EcomDev_PHPUnit_Test_Case_Config
{

    /**
     * Test classes are aliased correctly
     *
     * @test
     */
    public function testClassAliases()
    {
        $this->assertModelAlias('aligent_paypal/ipn', 'Aligent_Paypal_Model_Ipn');
        $this->assertModelAlias('paypal/ipn', 'Aligent_Paypal_Model_Ipn');
        $this->assertModelAlias('aligent_paypal/system_config_source_refundmethod', 'Aligent_Paypal_Model_System_Config_Source_Refundmethod');
        $this->assertHelperAlias('aligent_paypal/data', 'Aligent_Paypal_Helper_Data');
    }

}