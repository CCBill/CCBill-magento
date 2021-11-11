<?php
/**
 * @category    CCBill
 * @package     CCBill_CCBillPayment
 * @copyright   Copyright (c) 2014-2021 CCBill (http://ccbill.com)
 */
class CCBill_CCBillPayment_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
    
    
        $standard = Mage::getModel('ccbillpayment/standard');

        $form = new Varien_Data_Form();
        $form->setAction($standard->getConfig()->getCCBillPaymentUrl())
            ->setId('ccbillpayment_standard_checkout')
            ->setName('ccbillpayment_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($standard->getStandardCheckoutFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $idSuffix = Mage::helper('core')->uniqHash();
        $submitButton = new Varien_Data_Form_Element_Submit(array(
            'value'    => $this->__('Click here if you are not redirected within 10 seconds...'),
        ));
        $id = "submit_to_ccbillpayment_button_{$idSuffix}";
        $submitButton->setId($id);
        $form->addElement($submitButton);
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to the CCBill website in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("ccbillpayment_standard_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}
