<?php
/**
 * @category    CCBill
 * @package     CCBill_CCBillPayment
 * @copyright   Copyright (c) 2014-2021 CCBill (http://ccbill.com)
 */

/**
 * Unified IPN controller for all supported CCBill methods
 */
class CCBill_CCBillPayment_IpnController extends Mage_Core_Controller_Front_Action
{
    /**
     * Instantiate IPN model and pass IPN request to it
     */
    public function indexAction()
    {die('poo');
        if (!$this->getRequest()->isPost()) {
            return;
        }

        try {
            $data = $this->getRequest()->getPost();
            Mage::getModel('ccbillpayment/ipn')->processIpnRequest($data, new Varien_Http_Adapter_Curl());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}
