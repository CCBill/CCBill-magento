<?php
/**
 * @category    CCBill
 * @package     CCBill_CCBillPayment
 * @copyright   Copyright (c) 2014-2021 CCBill (http://ccbill.com)
 */

/**
 * Config model that is aware of all CCBill_CCBillPayment payment methods
 * Works with CCBill-specific system configuration
 */
class CCBill_CCBillPayment_Model_Config
{
    /**
     * CCBill Standard
     * @var string
     */
    const METHOD_WPS         = 'ccbillpayment';

    /**
     * Buttons and images
     * @var string
     */
    const EC_FLAVOR_DYNAMIC       = 'dynamic';
    const EC_FLAVOR_STATIC        = 'static';
    const EC_BUTTON_TYPE_SHORTCUT = 'ecshortcut';
    const EC_BUTTON_TYPE_MARK     = 'ecmark';
    const PAYMENT_MARK_37x23      = '37x23';
    const PAYMENT_MARK_50x34      = '50x34';
    const PAYMENT_MARK_60x38      = '60x38';
    const PAYMENT_MARK_180x113    = '180x113';
    const DEFAULT_LOGO_TYPE       = 'wePrefer_150x60';

    /**
     * Payment actions
     * @var string
     */
    const PAYMENT_ACTION_SALE  = 'Sale';
    const PAYMENT_ACTION_ORDER = 'Order';
    const PAYMENT_ACTION_AUTH  = 'Authorization';

    /**
     * Authorization amounts for Account Verification
     *
     * @deprecated since 1.6.2.0
     * @var int
     */
    const AUTHORIZATION_AMOUNT_ZERO = 0;
    const AUTHORIZATION_AMOUNT_ONE = 1;
    const AUTHORIZATION_AMOUNT_FULL = 2;

    /**
     * Require Billing Address
     * @var int
     */
    const REQUIRE_BILLING_ADDRESS_NO = 0;
    const REQUIRE_BILLING_ADDRESS_ALL = 1;
    const REQUIRE_BILLING_ADDRESS_VIRTUAL = 2;

    /**
     * Fraud management actions
     * @var string
     */
    const FRAUD_ACTION_ACCEPT = 'Acept';
    const FRAUD_ACTION_DENY   = 'Deny';

    /**
     * Refund types
     * @var string
     */
    const REFUND_TYPE_FULL = 'Full';
    const REFUND_TYPE_PARTIAL = 'Partial';

    /**
     * Express Checkout flows
     * @var string
     */
    const EC_SOLUTION_TYPE_SOLE = 'Sole';
    const EC_SOLUTION_TYPE_MARK = 'Mark';

    /**
     * Payment data transfer methods (Standard)
     *
     * @var string
     */
    const WPS_TRANSPORT_IPN      = 'ipn';
    const WPS_TRANSPORT_PDT      = 'pdt';
    const WPS_TRANSPORT_IPN_PDT  = 'ipn_n_pdt';

    /**
     * Billing Agreement Signup
     *
     * @var string
     */
    const EC_BA_SIGNUP_AUTO     = 'auto';
    const EC_BA_SIGNUP_ASK      = 'ask';
    const EC_BA_SIGNUP_NEVER    = 'never';


    /**
     * Current payment method code
     * @var string
     */
    protected $_methodCode = null;

    /**
     * Current store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * Instructions for generating proper BN code
     *
     * @var array
     */
    protected $_buildNotationPPMap = array(
        'ccbillpayment_standard'  => 'WPS'
    );

    /**
     * Style system config map (Express Checkout)
     *
     * @var array
     */
    protected $_ecStyleConfigMap = array(
        'page_style'    => 'page_style',
        'ccbill_hdrimg' => 'hdrimg',
        'ccbill_hdrbordercolor' => 'hdrbordercolor',
        'ccbill_hdrbackcolor'   => 'hdrbackcolor',
        'ccbill_payflowcolor'   => 'payflowcolor',
    );

    /**
     * Currency codes supported by CCBill methods
     *
     * @var array
     */
    protected $_supportedCurrencyCodes = array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN',
        'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD', 'TWD', 'THB');

    /**
     * Merchant country supported by CCBill
     *
     * @var array
     */
    protected $_supportedCountryCodes = array(
        'AE','AR','AT','AU','BE','BG','BR','CA','CH','CL','CR','CY','CZ','DE','DK','DO','EC','EE','ES','FI','FR','GB',
        'GF','GI','GP','GR','HK','HU','ID','IE','IL','IN','IS','IT','JM','JP','KR','LI','LT','LU','LV','MQ','MT','MX',
        'MY','NL','NO','NZ','PH','PL','PT','RE','RO','SE','SG','SI','SK','SM','TH','TR','TW','US','UY','VE','VN','ZA');

    /**
     * Buyer country supported by CCBill
     *
     * @var array
     */
    protected $_supportedBuyerCountryCodes = array(
        'AF ', 'AX ', 'AL ', 'DZ ', 'AS ', 'AD ', 'AO ', 'AI ', 'AQ ', 'AG ', 'AR ', 'AM ', 'AW ', 'AU ', 'AT ', 'AZ ',
        'BS ', 'BH ', 'BD ', 'BB ', 'BY ', 'BE ', 'BZ ', 'BJ ', 'BM ', 'BT ', 'BO ', 'BA ', 'BW ', 'BV ', 'BR ', 'IO ',
        'BN ', 'BG ', 'BF ', 'BI ', 'KH ', 'CM ', 'CA ', 'CV ', 'KY ', 'CF ', 'TD ', 'CL ', 'CN ', 'CX ', 'CC ', 'CO ',
        'KM ', 'CG ', 'CD ', 'CK ', 'CR ', 'CI ', 'HR ', 'CU ', 'CY ', 'CZ ', 'DK ', 'DJ ', 'DM ', 'DO ', 'EC ', 'EG ',
        'SV ', 'GQ ', 'ER ', 'EE ', 'ET ', 'FK ', 'FO ', 'FJ ', 'FI ', 'FR ', 'GF ', 'PF ', 'TF ', 'GA ', 'GM ', 'GE ',
        'DE ', 'GH ', 'GI ', 'GR ', 'GL ', 'GD ', 'GP ', 'GU ', 'GT ', 'GG ', 'GN ', 'GW ', 'GY ', 'HT ', 'HM ', 'VA ',
        'HN ', 'HK ', 'HU ', 'IS ', 'IN ', 'ID ', 'IR ', 'IQ ', 'IE ', 'IM ', 'IL ', 'IT ', 'JM ', 'JP ', 'JE ', 'JO ',
        'KZ ', 'KE ', 'KI ', 'KP ', 'KR ', 'KW ', 'KG ', 'LA ', 'LV ', 'LB ', 'LS ', 'LR ', 'LY ', 'LI ', 'LT ', 'LU ',
        'MO ', 'MK ', 'MG ', 'MW ', 'MY ', 'MV ', 'ML ', 'MT ', 'MH ', 'MQ ', 'MR ', 'MU ', 'YT ', 'MX ', 'FM ', 'MD ',
        'MC ', 'MN ', 'MS ', 'MA ', 'MZ ', 'MM ', 'NA ', 'NR ', 'NP ', 'NL ', 'AN ', 'NC ', 'NZ ', 'NI ', 'NE ', 'NG ',
        'NU ', 'NF ', 'MP ', 'NO ', 'OM ', 'PK ', 'PW ', 'PS ', 'PA ', 'PG ', 'PY ', 'PE ', 'PH ', 'PN ', 'PL ', 'PT ',
        'PR ', 'QA ', 'RE ', 'RO ', 'RU ', 'RW ', 'SH ', 'KN ', 'LC ', 'PM ', 'VC ', 'WS ', 'SM ', 'ST ', 'SA ', 'SN ',
        'CS ', 'SC ', 'SL ', 'SG ', 'SK ', 'SI ', 'SB ', 'SO ', 'ZA ', 'GS ', 'ES ', 'LK ', 'SD ', 'SR ', 'SJ ', 'SZ ',
        'SE ', 'CH ', 'SY ', 'TW ', 'TJ ', 'TZ ', 'TH ', 'TL ', 'TG ', 'TK ', 'TO ', 'TT ', 'TN ', 'TR ', 'TM ', 'TC ',
        'TV ', 'UG ', 'UA ', 'AE ', 'GB ', 'US ', 'UM ', 'UY ', 'UZ ', 'VU ', 'VE ', 'VN ', 'VG ', 'VI ', 'WF ', 'EH ',
        'YE ', 'ZM ', 'ZW'
    );

    /**
     * Locale codes supported by misc images (marks, shortcuts etc)
     */
    protected $_supportedImageLocales = array('de_DE', 'en_AU', 'en_GB', 'en_US', 'es_ES', 'es_XC', 'fr_FR',
        'fr_XC', 'it_IT', 'ja_JP', 'nl_NL', 'pl_PL', 'zh_CN', 'zh_XC',
    );

    /**
     * Set method and store id, if specified
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        if ($params) {
            $method = array_shift($params);
            $this->setMethod($method);
            if ($params) {
                $storeId = array_shift($params);
                $this->setStoreId($storeId);
            }
        }
    }

    /**
     * Method code setter
     *
     * @param string|Mage_Payment_Model_Method_Abstract $method
     * @return CCBill_CCBillPayment_Model_Config
     */
    public function setMethod($method)
    {
        if ($method instanceof Mage_Payment_Model_Method_Abstract) {
            $this->_methodCode = $method->getCode();
        } elseif (is_string($method)) {
            $this->_methodCode = $method;
        }
        return $this;
    }

    /**
     * Payment method instance code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * Store ID setter
     *
     * @param int $storeId
     * @return CCBill_CCBillPayment_Model_Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Check whether method active in configuration and supported for merchant country or not
     *
     * @param string $method Method code
     * @return bool
     */
    public function isMethodActive($method)
    {
        if ($this->isMethodSupportedForCountry($method)
            && Mage::getStoreConfigFlag("payment/{$method}/active", $this->_storeId)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check whether method available for checkout or not
     * Logic based on merchant country, methods dependence
     *
     * @param string $method Method code
     * @return bool
     */
    public function isMethodAvailable($methodCode = null)
    {
        if ($methodCode === null) {
            $methodCode = $this->getMethodCode();
        }

        $result = true;

        if (!$this->isMethodActive($methodCode)) {
            $result = false;
        }

        switch ($methodCode) {
            case self::METHOD_WPS:
                if (!$this->businessAccount) {
                    $result = false;
                    break;
                }
                // check for direct payments dependence
                if ($this->isMethodActive(self::METHOD_WPP_DIRECT)
                    || $this->isMethodActive(self::METHOD_WPP_PE_DIRECT)) {
                    $result = false;
                }
                break;
            case self::METHOD_WPP_EXPRESS:
                // check for direct payments dependence
                if ($this->isMethodActive(self::METHOD_WPP_PE_DIRECT)) {
                    $result = false;
                } elseif ($this->isMethodActive(self::METHOD_WPP_DIRECT)) {
                    $result = true;
                }
                break;
            case self::METHOD_BML:
                // check for express payments dependence
                if (!$this->isMethodActive(self::METHOD_WPP_EXPRESS)) {
                    $result = false;
                }
                break;
            case self::METHOD_WPP_PE_EXPRESS:
                // check for direct payments dependence
                if ($this->isMethodActive(self::METHOD_WPP_PE_DIRECT)) {
                    $result = true;
                } elseif (!$this->isMethodActive(self::METHOD_WPP_PE_DIRECT)
                          && !$this->isMethodActive(self::METHOD_PAYFLOWPRO)) {
                    $result = false;
                }
                break;
            case self::METHOD_WPP_PE_BML:
                // check for express payments dependence
                if (!$this->isMethodActive(self::METHOD_WPP_PE_EXPRESS)) {
                    $result = false;
                }
                break;
            case self::METHOD_BILLING_AGREEMENT:
                $result = $this->isWppApiAvailabe();
                break;
            case self::METHOD_WPP_DIRECT:
            case self::METHOD_WPP_PE_DIRECT:
                break;
        }
        return $result;
    }

    /**
     * Config field magic getter
     * The specified key can be either in camelCase or under_score format
     * Tries to map specified value according to set payment method code, into the configuration value
     * Sets the values into public class parameters, to avoid redundant calls of this method
     *
     * @param string $key
     * @return string|null
     */
    public function __get($key)
    {
        $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));
        $value = Mage::getStoreConfig($this->_getSpecificConfigPath($underscored), $this->_storeId);
        $value = $this->_prepareValue($underscored, $value);
        $this->$key = $value;
        $this->$underscored = $value;
        return $value;
    }

    /**
     * Perform additional config value preparation and return new value if needed
     *
     * @param string $key Underscored key
     * @param string $value Old value
     * @return string Modified value or old value
     */
    protected function _prepareValue($key, $value)
    {
        // Always set payment action as "Sale" for Unilateral payments in EC
        if ($key == 'payment_action'
            && $value != self::PAYMENT_ACTION_SALE
            && $this->_methodCode == self::METHOD_WPP_EXPRESS
            && $this->shouldUseUnilateralPayments())
        {
            return self::PAYMENT_ACTION_SALE;
        }
        return $value;
    }

    /**
     * Return merchant country codes supported by CCBill
     *
     * @return array
     */
    public function getSupportedMerchantCountryCodes()
    {
        return $this->_supportedCountryCodes;
    }

    /**
     * Return buyer country codes supported by CCBill
     *
     * @return array
     */
    public function getSupportedBuyerCountryCodes()
    {
        return $this->_supportedBuyerCountryCodes;
    }

    /**
     * Return merchant country code, use default country if it not specified in General settings
     *
     * @return string
     */
    public function getMerchantCountry()
    {
        $countryCode = Mage::getStoreConfig($this->_mapGeneralFieldset('merchant_country'), $this->_storeId);
        if (!$countryCode) {
            $countryCode = Mage::helper('core')->getDefaultCountry($this->_storeId);
        }
        return $countryCode;
    }

    /**
     * Check whether method supported for specified country or not
     * Use $_methodCode and merchant country by default
     *
     * @return bool
     */
    public function isMethodSupportedForCountry($method = null, $countryCode = null)
    {
        if ($method === null) {
            $method = $this->getMethodCode();
        }
        if ($countryCode === null) {
            $countryCode = $this->getMerchantCountry();
        }
        $countryMethods = $this->getCountryMethods($countryCode);
        if (in_array($method, $countryMethods)) {
            return true;
        }
        return false;
    }

    /**
     * Return list of allowed methods for specified country iso code
     *
     * @param string $countryCode 2-letters iso code
     * @return array
     */
    public function getCountryMethods($countryCode = null)
    {
        $countryMethods = array(
            'other' => array(
                self::METHOD_WPS,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'US' => array(
                self::METHOD_PAYFLOWADVANCED,
                self::METHOD_WPP_DIRECT,
                self::METHOD_WPS,
                self::METHOD_PAYFLOWPRO,
                self::METHOD_PAYFLOWLINK,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
                self::METHOD_WPP_PE_EXPRESS,
                self::METHOD_WPP_PE_BML,
            ),
            'CA' => array(
                self::METHOD_WPP_DIRECT,
                self::METHOD_WPS,
                self::METHOD_PAYFLOWPRO,
                self::METHOD_PAYFLOWLINK,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'GB' => array(
                self::METHOD_WPP_DIRECT,
                self::METHOD_WPS,
                self::METHOD_WPP_PE_DIRECT,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
                self::METHOD_WPP_PE_EXPRESS,
                self::METHOD_WPP_PE_BML,
            ),
            'AU' => array(
                self::METHOD_WPS,
                self::METHOD_PAYFLOWPRO,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'NZ' => array(
                self::METHOD_WPS,
                self::METHOD_PAYFLOWPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'JP' => array(
                self::METHOD_WPS,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'FR' => array(
                self::METHOD_WPS,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'IT' => array(
                self::METHOD_WPS,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'ES' => array(
                self::METHOD_WPS,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
            'HK' => array(
                self::METHOD_WPS,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BML,
                self::METHOD_BILLING_AGREEMENT,
            ),
        );
        if ($countryCode === null) {
            return $countryMethods;
        }
        return isset($countryMethods[$countryCode]) ? $countryMethods[$countryCode] : $countryMethods['other'];
    }

    /**
     * Return start url for CCBill Basic
     *
     * @param string $token
     * @return string
     */
    public function getCCBillBasicStartUrl($token)
    {
        $params = array(
            'cmd'   => '_express-checkout',
            'token' => $token,
        );

        return $this->getCCBillUrl($params);
    }

     /**
     * CCBill web URL generic getter
     *
     * @param array $params
     * @return string
     */
    public function getCCBillUrl(array $params = array())
    {
        if(Mage::getStoreConfig('payment/ccbillpayment/is_flexform')){
          return sprintf('https://api.ccbill.com/wap-frontflex/flexforms/',
                          $params ? http_build_query($params) : ''
                        );
        }
        else{
          return sprintf('https://bill.ccbill.com/jpost/signup.cgi',
                          $params ? '?' . http_build_query($params) : ''
                        );
        }// end if/else
    }

    /**
     * Get CCBill "mark" image URL
     * Supposed to be used on payment methods selection
     * $staticSize is applicable for static images only
     *
     * @param string $localeCode
     * @param float $orderTotal
     * @param string $pal
     * @param string $staticSize
     */
    public function getPaymentMarkImageUrl($localeCode, $orderTotal = null, $pal = null, $staticSize = null)
    {

        if (null === $staticSize) {
            $staticSize = $this->paymentMarkSize;
        }
        switch ($staticSize) {
            case self::PAYMENT_MARK_37x23:
            case self::PAYMENT_MARK_50x34:
            case self::PAYMENT_MARK_60x38:
            case self::PAYMENT_MARK_180x113:
                break;
            default:
                $staticSize = self::PAYMENT_MARK_37x23;
        }
        return sprintf('https://www.ccbill.com/common/img/logo_CCBill_home.png',
            $this->_getSupportedLocaleCode($localeCode), $staticSize);
    }

    /**
     * Check whether WPP API credentials are available for this method
     *
     * @return bool
     */
    public function isWppApiAvailabe()
    {
        return $this->api_username && $this->api_password && ($this->api_signature || $this->api_cert);
    }

    /**
     * Payment data delivery methods getter for CCBill Standard
     *
     * @return array
     */
    public function getWpsPaymentDeliveryMethods()
    {
        return array(
            self::WPS_TRANSPORT_IPN      => Mage::helper('adminhtml')->__('IPN (Instant Payment Notification) Only'),
            // not supported yet:
//            self::WPS_TRANSPORT_PDT      => Mage::helper('adminhtml')->__('PDT (Payment Data Transfer) Only'),
//            self::WPS_TRANSPORT_IPN_PDT  => Mage::helper('adminhtml')->__('Both IPN and PDT'),
        );
    }

    /**
     * Check whether the specified payment method is a CC-based one
     *
     * @param string $code
     * @return bool
     */
    public static function getIsCreditCardMethod($code)
    {
        switch ($code) {
            case self::METHOD_WPP_DIRECT:
            case self::METHOD_WPP_PE_DIRECT:
            case self::METHOD_PAYFLOWPRO:
            case self::METHOD_PAYFLOWLINK:
            case self::METHOD_PAYFLOWADVANCED:
            case self::METHOD_HOSTEDPRO:
                return true;
        }
        return false;
    }

    /**
     * Check whether specified currency code is supported
     *
     * @param string $code
     * @return bool
     */
    public function isCurrencyCodeSupported($code)
    {
        if (in_array($code, $this->_supportedCurrencyCodes)) {
            return true;
        }
        if ($this->getMerchantCountry() == 'BR' && $code == 'BRL') {
            return true;
        }
        if ($this->getMerchantCountry() == 'MY' && $code == 'MYR') {
            return true;
        }
        return false;
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _getSpecificConfigPath($fieldName)
    {
        $path = null;
        switch ($this->_methodCode) {
            case self::METHOD_WPS:
                $path = $this->_mapStandardFieldset($fieldName);
                break;
            case self::METHOD_BML:
                $path = $this->_mapBmlFieldset($fieldName);
                break;
            case self::METHOD_WPP_PE_BML:
                $path = $this->_mapBmlUkFieldset($fieldName);
                break;
            case self::METHOD_WPP_EXPRESS:
            case self::METHOD_WPP_PE_EXPRESS:
                $path = $this->_mapExpressFieldset($fieldName);
                break;
            case self::METHOD_WPP_DIRECT:
            case self::METHOD_WPP_PE_DIRECT:
                $path = $this->_mapDirectFieldset($fieldName);
                break;
            case self::METHOD_BILLING_AGREEMENT:
            case self::METHOD_HOSTEDPRO:
                $path = $this->_mapMethodFieldset($fieldName);
                break;
        }

        if ($path === null) {
            switch ($this->_methodCode) {
                case self::METHOD_WPP_EXPRESS:
                case self::METHOD_BML:
                case self::METHOD_WPP_DIRECT:
                case self::METHOD_BILLING_AGREEMENT:
                case self::METHOD_HOSTEDPRO:
                    $path = $this->_mapWppFieldset($fieldName);
                    break;
                case self::METHOD_WPP_PE_EXPRESS:
                case self::METHOD_WPP_PE_DIRECT:
                case self::METHOD_PAYFLOWADVANCED:
                case self::METHOD_PAYFLOWLINK:
                    $path = $this->_mapWpukFieldset($fieldName);
                    break;
            }
        }

        if ($path === null) {
            $path = $this->_mapGeneralFieldset($fieldName);
        }
        if ($path === null) {
            $path = $this->_mapGenericStyleFieldset($fieldName);
        }
        return $path;
    }

    /**
     * Check wheter specified country code is supported by build notation codes for specific countries
     *
     * @param $code
     * @return string|null
     */
    private function _matchBnCountryCode($code)
    {
        switch ($code) {
            // GB == UK
            case 'GB':
                return 'UK';
            // Australia, Austria, Belgium, Canada, China, France, Germany, Hong Kong, Italy
            case 'AU': case 'AT': case 'BE': case 'CA': case 'CN': case 'FR': case 'DE': case 'HK': case 'IT':
            // Japan, Mexico, Netherlands, Poland, Singapore, Spain, Switzerland, United Kingdom, United States
            case 'JP': case 'MX': case 'NL': case 'PL': case 'SG': case 'ES': case 'CH': case 'UK': case 'US':
                return $code;
        }
    }
}

