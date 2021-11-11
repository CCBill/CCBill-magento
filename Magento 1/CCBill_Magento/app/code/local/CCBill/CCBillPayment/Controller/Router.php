<?php
/**
 * @author CCBill (ccbill.com
 */
  class CCBill_CCBillPayment_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard{

    /**
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        $a = '';

        $isCCBillRouter = strpos($_SERVER['REQUEST_URI'], 'ccbillpayment/router') !== false;

        if($isCCBillRouter && $_REQUEST['Action']){

          $a = $_REQUEST['Action'];

          switch($a){
            case "CheckoutSuccess": $this->CheckoutSuccess();
              break;
            case "CheckoutFailure": $this->CheckoutFailure();
              break;
            case "Approval_Post":   $this->Approval_Post();
              break;
            case "Denial_Post":     $this->Denial_Post();
              break;
            default:
              die('Unauthorized');
          }// end switch

        }
        else if($isCCBillRouter){
          die('Unauthorized');
        }// end if/else

    }// end match

    public function CheckoutSuccess(){

      // Temporary until we can figure out how to get the order id
      $tUrl = Mage::getUrl() . 'customer/account/index/';
      print('<html><head><title>Redirecting</title>');
      //print('<script type="text/javascript">setTimeout(function(){document.location="' . $tUrl . '";}, 3000);</script>');
      print('</head><body><div>Your order has been completed successfully.  You will receive an email containing your order details.</div>');
      print('<div><a href="' . Mage::getUrl() . '">Return to store home</a></div>');
      print('<div><a href="' . $tUrl . '">Go to account summary</a></div>');
      //print_r('orderid1: ' . $_COOKIE["OrderId"]);
      //print_r('; orderid2: ' . $HTTP_COOKIE_VARS["OrderId"]);

      print('</body></html>');
      die('');

    }// end CheckoutSuccess

    public function HandleUnauthorized(){
      print('<html><head><title>Unauthorized</title></head><body>Unauthorized</body></html>');//<ul>');
    }// end HandleUnauthorized

    public function CheckoutFailure(){
      die('Checkout Failure');
    }// end CheckoutFailure

    public function Approval_Post(){

      require_once("app/Mage.php");

      $app = Mage::app('');

      $salesModel=Mage::getModel("sales/order");
      $salesCollection = $salesModel->getCollection()->addFieldToFilter('status', 'pending');


      $quoteId = -1;
      $quoteId = $_REQUEST['wc_qid'];
      $myOrderId = -1;//$_REQUEST['wc_orderid'];

      // Get Order
      $myOrder = null;//Mage::getModel('sales/order')->load($myOrderId);;
      $myPayment = null;
      $myInvoice = null;

      // From CCBIll
      $myFirstName    = '';
      $myLastName     = '';
      $myEmail        = '';
      $myAmount       = '';
      $myCurrencyCode = 0;
      $myDigest       = '';

      print('<html><head><title>Approval Post</title></head><body>');//<ul>');

      foreach($salesCollection as $order)
      {
        //print('<div>Searching order: ' . $order->entity_id . ' : QuoteId: ' . $order->quote_id . '</div>');
        if($order->quote_id == $quoteId){
          //print('<div>Found order.  ID: ' . $order->entity_id);
          $myOrderId = $order->entity_id;
          $myOrder = $order;
          break;
        }// end if

      }// end foreach

      $txId      = 0;
      $mySuccess = 0;

      if($myAction == 'Approval_Post'){
        if(isset($_POST['subscription_id'])) $txId = $_POST['subscription_id'];
        $mySuccess = 1;
      }
      else if($myAction == 'Denial_Post'){
        $mySuccess = 0;
      }// end if/else
      /*
      // Just for testing
      if($mySuccess == 0){
        if($myAction == 'Approval_Post'){
          if(isset($_REQUEST['subscription_id'])) $txId = $_REQUEST['subscription_id'];
          $mySuccess = 1;
        }
        else if($myAction == 'Denial_Post'){
          $mySuccess = 0;
        }// end if/else
      }// end if
      */
      // http://www.blueorchidd.com/test/magento/index.php/ccbillpayment/router/index/?Action=Approval_Post&wc_orderid=33&subscription_id=123&customer_fname=Testy&customer_lname=Testerson&email=testy@test.com&initialPrice=36.21&currencyCode=840&responseDigest=WEFNAFWFEW

      if(isset($_POST['customer_fname'])) $myFirstName    = $_POST['customer_fname'];
      if(isset($_POST['customer_lname'])) $myLastName     = $_POST['customer_lname'];
      if(isset($_POST['email']))          $myEmail        = $_POST['email'];
      if(isset($_POST['initialPrice']))   $myAmount       = $_POST['initialPrice'];
      if(isset($_POST['currencyCode']))   $myCurrencyCode = $_POST['currencyCode'];
      if(isset($_POST['responseDigest'])) $myDigest       = $_POST['responseDigest'];
      /*
      // Just for testing Request
      if(isset($_REQUEST['customer_fname'])) $myFirstName    = $_REQUEST['customer_fname'];
      if(isset($_REQUEST['customer_lname'])) $myLastName     = $_REQUEST['customer_lname'];
      if(isset($_REQUEST['email']))          $myEmail        = $_REQUEST['email'];
      if(isset($_REQUEST['initialPrice']))   $myAmount       = $_REQUEST['initialPrice'];
      if(isset($_REQUEST['currencyCode']))   $myCurrencyCode = $_REQUEST['currencyCode'];
      if(isset($_REQUEST['responseDigest'])) $myDigest       = $_REQUEST['responseDigest'];
      */
      if($myOrder != null){
        $myPayment = $myOrder->getPayment();

        $myPayment->setTransactionId($txId)
                  ->setIsTransactionClosed(0)
                  ->registerCaptureNotification($myAmount);

        print('<div>Payment: ' . $myPayment->getMethodInstance()->getTitle() . '</div>');

        $myOrder->save();

        print('<div>Order Saved</div>');

        $myInvoice = $myPayment->getCreatedInvoice();

        // Send customer email
        if($myInvoice && !$myOrder->getEmailSent()){

          $myOrder->sendNewOrderEmail()->addStatusHistoryComment(
                'CCBill payment complete.  Notified customer about invoice #' . $myInvoice->getIncrementId() . '.'
            )
            ->setIsCustomerNotified(true)
            //->setData('state', 'pending')
            //->setStatus('pending')
            ->addStatusHistoryComment('CCBill payment completed.', false)
            ->save();


            print('<div style="font-weight: bold;">IncId: ' . $myInvoice->getIncrementId() . '</div>');
          //$myOrder->save();
        }// end if

        print('<div>Quote ID: ' . $quoteId . '</div>');

        if($orderId >= 0)
          print('<div>Order ID: ' . $myOrderId . '</div>');
          print('<div>Order ID from order: ' . $myOrder->getIncrementId() . '</div>');
          print('<div>Invoice ID: ' . $myInvoice->getIncrementId() . '</div>');
        }// end if/else

      else{
        print('<div>Unable to locate order.</div>');
      }// end if/else

      print('</body></html>');
      die('');

    }// end Approval_Post

  }// end class
?>
