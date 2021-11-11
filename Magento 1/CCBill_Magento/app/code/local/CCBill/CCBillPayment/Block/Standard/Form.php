<?php
/**
 * @category    CCBill
 * @package     CCBill_CCBillPayment
 * @copyright   Copyright (c) 2014-2021 CCBill (http://ccbill.com)
 */

/**
 * CCBill Standard payment "form"
 */
class CCBill_CCBillPayment_Block_Standard_Form extends Mage_Paygate_Block_Form
{
    protected $_methodCode = CCBill_CCBillPayment_Model_Config::METHOD_WPS;

    protected $_config;

    protected function _construct()
    {
        $this->_config = Mage::getModel('ccbillpayment/config')->setMethod($this->getMethodCode());
        $locale = Mage::app()->getLocale();
        $mark = Mage::getConfig()->getBlockClassName('local/template');
        $mark = new $mark;
        $mark->setTemplate('ccbillpayment/standard/mark.phtml')
            ->setPaymentAcceptanceMarkHref($this->_config->getPaymentMarkWhatIsCCBillUrl($locale))
            ->setPaymentAcceptanceMarkSrc($this->_config->getPaymentMarkImageUrl($locale->getLocaleCode()))
        ; // known issue: code above will render only static mark image
        $this->setTemplate('ccbillpayment/standard/redirect.phtml')
            ->setRedirectMessage(
                Mage::helper('ccbillpayment')->__('You will be redirected to the CCBill website when you place an order.')
            )
            ->setMethodTitle('') // Output CCBill mark, omit title
            ->setMethodLabelAfterHtml($mark->toHtml())
        ;
        return parent::_construct();
    }


    public function getMethodCode()
    {
        return $this->_methodCode;
    }
}
