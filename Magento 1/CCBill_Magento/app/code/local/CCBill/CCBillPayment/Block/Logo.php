<?php
/**
 * @category    CCBill
 * @package     CCBill_CCBillPayment
 * @copyright   Copyright (c) 2014-2021 CCBill (http://ccbill.com)
 */

/**
 * CCBill online logo with additional options
 */
class CCBill_CCBillPayment_Block_Logo extends Mage_Core_Block_Template
{
    /**
     * Return URL for CCBillPayment Landing page
     *
     * @return string
     */
    public function getAboutCCBillPageUrl()
    {
        return $this->_getConfig()->getPaymentMarkWhatIsCCBillUrl(Mage::app()->getLocale());
    }

    /**
     * Getter for CCBill config
     *
     * @return CCBill_CCBillPayment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('ccbillpayment/config');
    }

    /**
     * Disable block output if logo turned off
     *
     * @return string
     */
    protected function _toHtml()
    {
        $type = $this->getLogoType(); // assigned in layout etc.
        $logoUrl = $this->_getConfig()->getAdditionalOptionsLogoUrl(Mage::app()->getLocale()->getLocaleCode(), $type);
        if (!$logoUrl) {
            return '';
        }
        $this->setLogoImageUrl($logoUrl);
        return parent::_toHtml();
    }
}
