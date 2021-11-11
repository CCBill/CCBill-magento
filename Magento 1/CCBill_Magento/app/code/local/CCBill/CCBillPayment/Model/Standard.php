<?php
/**
 * CCBill
 *
 * @category    CCBill
 * @package     CCBill_CCBillPayment
 * @copyright   Copyright (c) 2014-2021 CCBill (http://ccbill.com)
 */

/**
 *
 * CCBill Standard Module
 */
class CCBill_CCBillPayment_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = CCBill_CCBillPayment_Model_Config::METHOD_WPS;

     // Is this payment method a gateway (online auth/charge) ?
    protected $_isGateway               = true;

    //Can authorize online?
    protected $_canAuthorize            = false;

    // Can capture funds online?
    protected $_canCapture              = true;

    // Can capture partial amounts online?
    protected $_canCapturePartial       = false;

    // Can refund online?
    protected $_canRefund               = false;

    // Can void transactions online?
    protected $_canVoid                 = false;

    // Can use this payment method in administration panel?
    protected $_canUseInternal          = false;

    // Can show this payment method as an option on checkout payment page?
    protected $_canUseCheckout          = true;

    // Is this payment method suitable for multi-shipping checkout?
    protected $_canUseForMultishipping  = false;

    // Can save credit card information for future processing?
    protected $_canSaveCc = false;

    protected $_isInitializeNeeded      = true;


     /**
     * CCBill web URL generic getter
     *
     * @param array $params
     * @return string
     */
    public function getCCBillUrl(array $params = array())
    {
        if(Mage::getStoreConfig('payment/ccbillpayment/is_flexform')){
          return sprintf('https://api.ccbill.com/wap-frontflex/flexforms/' . Mage::getStoreConfig('payment/ccbillpayment/formname'),
                          $params ? http_build_query($params) : ''
                        );
        }
        else{
          return sprintf('https://bill.ccbill.com/jpost/signup.cgi',
                          $params ? '?' . http_build_query($params) : ''
                        );
        }// end if/else
    }

    public function processSuccess(array $postData){
      $salesModel=Mage::getModel("sales/order");
      $salesCollection = $salesModel->getCollection();

      $orderId = -1;
      //$quoteId = -1;
      //$quoteId = $_REQUEST['wc_orderid'];
      $customer = Mage::getSingleton('customer/session')->getCustomer();
      $address = $customer->getDefaultBillingAddress();
      $session = Mage::getSingleton('checkout/session');
      $quoteId = $session->getQuoteId();

      //$quoteId = Mage::getSingleton('core/session')->getQuoteId();

      //die('QuoteId: ' . $quoteId . '; Customer FirstName: ' . $address->firstname);
    }// end processSuccess

    /**
     * Payment additional information key for payment action
     * @var string
     */

    /**
     * Config instance getter
     * @return CCBill_CCBillPayment_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->_code);
            if ($store = $this->getStore()) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }
            $this->_config = Mage::getModel('ccbillpayment/config', $params);
        }
        return $this->_config;
    }


    /**
     * Authorize payment not supported
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return CCBill_CCBillPayment_Model_Standard
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        return false;
    }

    /**
     * Void payment not available
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return CCBill_CCBillPayment_Model_Standard
     */
    public function void(Varien_Object $payment)
    {
        return false;
    }

    /**
     * Capture payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return CCBill_CCBillPayment_Model_Standard
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $voided = false;

        $this->_placeOrder($payment, $amount);

        return $this;
    }

    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return CCBill_CCBillPayment_Model_Standard
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return CCBill_CCBillPayment_Model_Standard
     */
    public function cancel(Varien_Object $payment)
    {
        $this->void($payment);

        return $this;
    }

    /**
     * Place an order with capture action
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return CCBill_CCBillPayment_Model_Standard
     */
    protected function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $order = $payment->getOrder();

        // prepare api call
        $token = $payment->getAdditionalInformation(CCBill_CCBillPayment_Model_Standard_Checkout::PAYMENT_INFO_TRANSPORT_TOKEN);
        $api = $this->_pro->getApi()
            ->setToken($token)
            ->setPayerId($payment->
                getAdditionalInformation(CCBill_CCBillPayment_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID))
            ->setAmount($amount)
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setNotifyUrl(Mage::getUrl('ccbillpayment/ipn/'))
            ->setInvNum($order->getIncrementId())
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setCCBillPaymentCart(Mage::getModel('ccbillpayment/cart', array($order)))
            ->setIsLineItemsEnabled($this->_pro->getConfig()->lineItemsEnabled);

        // call api and get details from it
        $api->callDoExpressCheckoutPayment();

        $this->_importToPayment($api, $payment);
        return $this;
    }

    protected function getStateCodeFromName($stateName){

      $rVal = $stateName;

      switch($rVal){
        case 'Alabama':         $rVal = 'AL';
          break;
        case 'Alaska':          $rVal = 'AK';
          break;
        case 'Arizona':         $rVal = 'AZ';
          break;
        case 'Arkansas':        $rVal = 'AR';
          break;
        case 'California':      $rVal = 'CA';
          break;
        case 'Colorado':        $rVal = 'CO';
          break;
        case 'Connecticut':     $rVal = 'CT';
          break;
        case 'Delaware':        $rVal = 'DE';
          break;
        case 'Florida':         $rVal = 'FL';
          break;
        case 'Georgia':         $rVal = 'GA';
          break;
        case 'Hawaii':          $rVal = 'HI';
          break;
        case 'Idaho':           $rVal = 'ID';
          break;
        case 'Illinois':        $rVal = 'IL';
          break;
        case 'Indiana':         $rVal = 'IN';
          break;
        case 'Iowa':            $rVal = 'IA';
          break;
        case 'Kansas':          $rVal = 'KS';
          break;
        case 'Kentucky':        $rVal = 'KY';
          break;
        case 'Louisiana':       $rVal = 'LA';
          break;
        case 'Maine':           $rVal = 'ME';
          break;
        case 'Maryland':        $rVal = 'MD';
          break;
        case 'Massachusetts':   $rVal = 'MA';
          break;
        case 'Michigan':        $rVal = 'MI';
          break;
        case 'Minnesota':       $rVal = 'MN';
          break;
        case 'Mississippi':     $rVal = 'MS';
          break;
        case 'Missouri':        $rVal = 'MO';
          break;
        case 'Montana':         $rVal = 'MT';
          break;
        case 'Nebraska':        $rVal = 'NE';
          break;
        case 'Nevada':          $rVal = 'NV';
          break;
        case 'New Hampshire':   $rVal = 'NH';
          break;
        case 'New Jersey':      $rVal = 'NJ';
          break;
        case 'New Mexico':      $rVal = 'NM';
          break;
        case 'New York':        $rVal = 'NY';
          break;
        case 'North Carolina':  $rVal = 'NC';
          break;
        case 'North Dakota':    $rVal = 'ND';
          break;
        case 'Ohio':            $rVal = 'OH';
          break;
        case 'Oklahoma':        $rVal = 'OK';
          break;
        case 'Oregon':          $rVal = 'OR';
          break;
        case 'Pennsylvania':    $rVal = 'PN';
          break;
        case 'Rhode Island':    $rVal = 'RI';
          break;
        case 'South Carolina':  $rVal = 'SC';
          break;
        case 'South Dakota':    $rVal = 'SD';
          break;
        case 'Tennessee':       $rVal = 'TN';
          break;
        case 'Texas':           $rVal = 'TX';
          break;
        case 'Utah':            $rVal = 'UT';
          break;
        case 'Virginia':        $rVal = 'VA';
          break;
        case 'Vermont':         $rVal = 'VT';
          break;
        case 'Washington':      $rVal = 'WA';
          break;
        case 'Wisconsin':       $rVal = 'WI';
          break;
        case 'West Virginia':   $rVal = 'WV';
          break;
        case 'Wyoming':         $rVal = 'WY';
          break;
      }// end switch

      return $rVal;

    }// end getStateCodeFromName


    // Return the CCBill currency code
    // based on user selection
    protected function setCurrencyCode(){
      switch($this->Currency){
        case "USD": $this->CurrencyCode = 840;
          break;
        case "EUR": $this->CurrencyCode = 978;
          break;
        case "AUD": $this->CurrencyCode = 036;
          break;
        case "CAD": $this->CurrencyCode = 124;
          break;
        case "GBP": $this->CurrencyCode = 826;
          break;
        case "JPY": $this->CurrencyCode = 392;
          break;
      }// end switch
    }// end getCurrencyCode

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {

        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);

    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
          // This is hit after Place Order is clicked.
          //die('poopies');

          //return Mage::getUrl('ccbillpayment/redirect', array('_secure' => true));

          // Throw an error if the amount is not greater than zero
          if ( !(Mage::helper('checkout/cart')->getQuote()->getGrandTotal() > 0) )
            die("Invalid amount");


          $customer = Mage::getSingleton('customer/session')->getCustomer();
          $address = $customer->getDefaultBillingAddress();
          $session = Mage::getSingleton('checkout/session');
          $quoteId = $session->getQuoteId();


          $billingPeriodInDays = 2;
          $currencyCode = Mage::getStoreConfig('payment/ccbillpayment/currencycode');
          $salt         = Mage::getStoreConfig('payment/ccbillpayment/salt');

          $quote        = Mage::getModel('checkout/session')->getQuote();
          $quoteData    = $quote->getData();
          //$grandTotal   = '' . number_format($quoteData['grand_total'], 2, '.', '');
          $grandTotal   = '' . number_format (Mage::helper('checkout/cart')->getQuote()->getGrandTotal(), 2, '.', '');

          if(strrpos($grandTotal, '.') && strlen($grandTotal) > (strrpos($grandTotal, '.')+3)){
            $grandTotal = substr($grandTotal, 0, strlen($grandTotal)-2);
          }// end if decimal is present in total

          if (strpos($grandTotal, '0.00') !== false) {
            return sprintf($ccUrl);
          }

          $stringToHash = '' . $grandTotal
  	                         . $billingPeriodInDays
  	                         . $currencyCode
  	                         . $salt;

  	     $myDigest = md5($stringToHash);

  	     // --------- Get Order

  	     $salesModel=Mage::getModel("sales/order");
         $salesCollection = $salesModel->getCollection()->addFieldToFilter('status', 'pending');

  	     // Get Order
         $myOrder = null;
         $myOrderId = -1;
         $myPayment = null;
         $myInvoice = null;

         foreach($salesCollection as $order)
         {
           if($order->quote_id == $quoteId){
             $myOrderId = $order->entity_id;
             $myOrder = $order;
             break;
            }// end if
         }/// end foreach

  	     if($myOrder){

  	       $address = $order->getBillingAddress();

    	     $myOrder
              ->addStatusHistoryComment('Order pending CCBill payment.', false)
              ->save();

         }// end if
         //setcookie("OrderId", $myOrderId, time()+3600);

        // ----------- End Get Order

  	     //Mage::getSingleton('core/session')->setQuoteId($quoteId);

          // https://bill.ccbill.com/jpost/signup.cgi?clientAccnum=123456&clientSubacc=1234&formName=201cc&currencyCode=840&formPrice=36.2100&formPeriod=2&customer_fname=Testy&customer_lname=Test&email=test@test.com&zipcode=46818&country=US&state=24&wc_orderid=41&formDigest=dbd9ec9625037f17a3131c6f48f68c7e

          $ccUrl = $this->getCCBillUrl() . '?'
                 . 'clientAccnum='    . Mage::getStoreConfig('payment/ccbillpayment/client_account_number')
                 . '&wc_orderid='     . $myOrder->getIncrementId()
                 . '&wc_qid='         . $quoteId
                 . '&clientSubacc='   . Mage::getStoreConfig('payment/ccbillpayment/client_subaccount_number')
                 . '&formName='       . Mage::getStoreConfig('payment/ccbillpayment/formname')
                 . '&currencyCode='   . $currencyCode
                 . '&formPrice='      . $grandTotal
                 . '&formPeriod='     . $billingPeriodInDays
                 . '&initialPrice='   . $grandTotal
                 . '&initialPeriod='  . $billingPeriodInDays
                 . '&customer_fname=' . $address->firstname
                 . '&customer_lname=' . $address->lastname
                 . '&email='          . $address->email
                 . '&zipcode='        . $address->postcode
                 . '&country='        . $address->country_id
                 . '&state='          . $this->getStateCodeFromName($address->region)
                 . '&city='           . $address->city
                 . '&address1='       . $address->street
                 . '&formDigest='     . $myDigest;

                 //. '&referingDestURL=http://localhost/woo/finish';


          //return Mage::getUrl('ccbillpayment/ipn', array('_secure' => true));
          return sprintf($ccUrl);



    }
    /*
    // Return the CCBill currency code
    // based on user selection
    function setCurrencyCode(){
      switch($this->Currency){
        case "USD": $this->CurrencyCode = 840;
          break;
        case "EUR": $this->CurrencyCode = 978;
          break;
        case "AUD": $this->CurrencyCode = 036;
          break;
        case "CAD": $this->CurrencyCode = 124;
          break;
        case "GBP": $this->CurrencyCode = 826;
          break;
        case "JPY": $this->CurrencyCode = 392;
          break;
      }// end switch
    }// end getCurrencyCode
    */
}// end class

class CCBill_CCBillPayment_Model_System_Config_CurrencyCode_Values
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '840',
                'label' => 'USD'
            ),
            array(
                'value' => '978',
                'label' => 'EUR'
            ),
            array(
                'value' => '036',
                'label' => 'AUD'
            ),
            array(
                'value' => '124',
                'label' => 'CAD'
            ),
            array(
                'value' => '826',
                'label' => 'GBP'
            ),
            array(
                'value' => '392',
                'label' => 'JPY'
            )
        );
    }
}

?>
