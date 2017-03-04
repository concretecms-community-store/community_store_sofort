<?php
namespace Concrete\Package\CommunityStoreSofort\Src\CommunityStore\Payment\Methods\CommunityStoreSofort;

use Core;
use URL;
use Config;
use Session;
use Log;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;


class CommunityStoreSofortPaymentMethod extends StorePaymentMethod
{
    public function dashboardForm()
    {
        $this->set('sofortConfigKey',Config::get('community_store_sofort.sofortConfigKey'));
        $this->set('sofortTestMode',Config::get('community_store_sofort.sofortTestMode'));
        $this->set('sofortReason',Config::get('community_store_sofort.sofortReason'));
        $this->set('sofortInstruction',Config::get('community_store_sofort.sofortInstruction'));
        $this->set('sofortCurrency',Config::get('community_store_sofort.sofortCurrency'));
        $currencies = array(
            'EUR' => "Euro",
            'PLN' => "Polish Zloty",
            'GBP' => "Pound Sterling",
            'CHF' => "Swiss Franc"
        );
        $this->set('currencies',$currencies);
        $this->set('form',Core::make("helper/form"));
    }
    
    public function save(array $data = [])
    {
        Config::save('community_store_sofort.sofortConfigKey',$data['sofortConfigKey']);
        Config::save('community_store_sofort.sofortCurrency',$data['sofortCurrency']);
        Config::save('community_store_sofort.sofortReason',$data['sofortReason']);
        Config::save('community_store_sofort.sofortInstruction',$data['sofortInstruction']);
    }
    public function validate($args,$e)
    {
        $pm = StorePaymentMethod::getByHandle('community_store_sofort');
        if($args['paymentMethodEnabled'][$pm->getID()]==1){
            if($args['sofortConfigKey']==""){
                $e->add(t("Configuration Key must be set"));
            }
        }
        return $e;
        
    }

    public function redirectForm()
    {

        $order = StoreOrder::getByID(Session::get('orderID'));

        $currencyCode = Config::get('community_store_sofort.sofortCurrency');
        if(!$currencyCode){
            $currencyCode = "EUR";
        }

        $configkey = Config::get('community_store_sofort.sofortConfigKey');
        $reason = Config::get('community_store_sofort.sofortReason');

        if (!$reason) {
            $reason = Config::get('concrete.site');
        }


        $Sofortueberweisung = new \Sofort\SofortLib\Sofortueberweisung($configkey);
        // $Sofortueberweisung->setLogEnabled();
        $Sofortueberweisung->setAmount($order->getTotal());
        $Sofortueberweisung->setCurrencyCode($currencyCode);
        $Sofortueberweisung->setReason($reason,'REF-'.Session::get('orderID'));
        $Sofortueberweisung->setSuccessUrl(URL::to('/checkout/complete'), true); // i.e. http://my.shop/order/success
        $Sofortueberweisung->setAbortUrl(URL::to('/checkout/'));
        // $Sofortueberweisung->setSenderSepaAccount('SFRTDE20XXX', 'DE06000000000023456789', 'Max Mustermann');
        // $Sofortueberweisung->setSenderCountryCode('DE');

        $Sofortueberweisung->setNotificationUrl(URL::to('/checkout/sofortresponse'));
        //$Sofortueberweisung->setCustomerprotection(true);
        $Sofortueberweisung->sendRequest();
        if($Sofortueberweisung->isError()) {
            // SOFORT-API didn't accept the data
            $this->set('error',$Sofortueberweisung->getError());
        } else {
            // get unique transaction-ID useful for check payment status
            $transactionId = $Sofortueberweisung->getTransactionId();

            $order->saveTransactionReference($transactionId);
            // buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
            $paymentUrl = $Sofortueberweisung->getPaymentUrl();
            header('Location: '.$paymentUrl);
            exit();
        }


        $customer = new StoreCustomer();

        $this->set('siteName',Config::get('concrete.site'));
        $this->set('customer', $customer);
        $this->set('total',$order->getTotal());
        $this->set('notifyURL',URL::to('/checkout/sofortresponse'));
        $this->set('orderID',$order->getOrderID());
        $this->set('returnURL',URL::to('/checkout/complete'));
        $this->set('cancelReturn',URL::to('/checkout'));

        $this->set('currencyCode',$currencyCode);
    }
    
    public function submitPayment()
    {
        //nothing to do except return true
        return array('error'=>0, 'transactionReference'=>'');
        
    }

    public function getAction()
    {
       return false;
    }


    public static function validateCompletion()
    {

        $configkey = Config::get('community_store_sofort.sofortConfigKey');
        $SofortLib_Notification = new \Sofort\SofortLib\Notification();

        $TestNotification = $SofortLib_Notification->getNotification(file_get_contents('php://input'));

        $SofortLibTransactionData = new \Sofort\SofortLib\TransactionData($configkey);

        // If SofortLib_Notification returns a transaction_id:
        $SofortLibTransactionData->addTransaction($TestNotification);
        $SofortLibTransactionData->setApiVersion('2.0');
        $SofortLibTransactionData->sendRequest();

        // uncomment for debugging
//        $output = array();
//        $methods = array(
//            'getAmount' => '',
//            'getAmountRefunded' => '',
//            'getCount' => '',
//            'getPaymentMethod' => '',
//            'getConsumerProtection' => '',
//            'getStatus' => '',
//            'getStatusReason' => '',
//            'getStatusModifiedTime' => '',
//            'getLanguageCode' => '',
//            'getCurrency' => '',
//            'getTransaction' => '',
//            'getReason' => array(0,0),
//            'getUserVariable' => 0,
//            'getTime' => '',
//            'getProjectId' => '',
//            'getRecipientHolder' => '',
//            'getRecipientAccountNumber' => '',
//            'getRecipientBankCode' => '',
//            'getRecipientCountryCode' => '',
//            'getRecipientBankName' => '',
//            'getRecipientBic' => '',
//            'getRecipientIban' => '',
//            'getSenderHolder' => '',
//            'getSenderAccountNumber' => '',
//            'getSenderBankCode' => '',
//            'getSenderCountryCode' => '',
//            'getSenderBankName' => '',
//            'getSenderBic' => '',
//            'getSenderIban' => '',
//        );
//
//        foreach($methods as $method => $params) {
//            if(count($params) == 2) {
//                $output[] = $method . ': ' . $SofortLibTransactionData->$method($params[0], $params[1]);
//            } else if($params !== '') {
//                $output[] = $method . ': ' . $SofortLibTransactionData->$method($params);
//            } else {
//                $output[] = $method . ': ' . $SofortLibTransactionData->$method();
//            }
//        }

        if($SofortLibTransactionData->isError()) {
            Log::addError('SOFORT ERROR: '. $SofortLibTransactionData->getError());
        } elseif ($SofortLibTransactionData->getAmount() > 0) {

           // Log::addDebug('SOFORT DEBUG: '. print_r($output, true));
            $transReference = $SofortLibTransactionData->getTransaction();

            $em = \ORM::entityManager();
            $order = $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order')->findOneBy(array('transactionReference' => $transReference));

            if ($order) {
                $order->completeOrder();
            }
        }
    }

    public function checkoutForm()
    {
        $pmID = StorePaymentMethod::getByHandle('community_store_sofort')->getID();
        $this->set('pmID',$pmID);
    }

    public function getPaymentMinimum() {
        return 0.5;
    }

    public function getName()
    {
        return 'SOFORT';
    }

    public function isExternal() {
        return true;
    }
}
