<?php



class Softwarehtec_Cardcom_Model_Mysql4_Api_Debug extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('softcardcom/api_debug', 'debug_id');
    }
}