<?php
/**
 * PAYDOLLAR payment method class
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: PAYDOLLAR.php 6666 2007-08-15 06:03:24Z ajeh $
 */
/*
***author: Gayward S. Mendoza, E.c.E.
***mentor: Henry Kwan
***file:paydollar.php
***date: January 22, 2009
*** revised: May 21, 2009
***location: includes\modules\payment
 */
class paydollar extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code;
  /**
   * $title is the displayed name for this payment method
   *
   * @var string
   */
  var $title;
  /**
   * $description is a soft name for this payment method
   *
   * @var string
   */
  var $description;
  /**
   * $enabled determines whether this module shows or not... in catalog.
   *
   * @var boolean
   */
  var $enabled;
  /**
   * @return PAYDOLLAR
   */
  function paydollar() {
    global $order;

   $this->code = 'paydollar';
    $this->signature = 'paydollar|paydollar|1.0|2.2';
    if ($_GET['main_page'] != '') {
      $this->title = MODULE_PAYMENT_PAYDOLLAR_TEXT_CATALOG_TITLE; // Payment module title in Catalog
    } else {
      $this->title = MODULE_PAYMENT_PAYDOLLAR_TEXT_ADMIN_TITLE; // Payment module title in Admin
    }
    $this->description = MODULE_PAYMENT_PAYDOLLAR_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_PAYMENT_PAYDOLLAR_STATUS == 'True') ? true : false);
    $this->sort_order = MODULE_PAYMENT_PAYDOLLAR_SORT_ORDER;

    if ((int)MODULE_PAYMENT_PAYDOLLAR_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_PAYDOLLAR_ORDER_STATUS_ID;
    }

    if (is_object($order)) $this->update_status();
 
  //$this->form_action_url = DIR_WS_CATALOG . 'asiapay_checkout_process.php' ; //this is used only for testing.
  //$this->form_action_url = MODULE_PAYMENT_PAYDOLLAR_HANDLER;
  $this->form_action_url  = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
  }
  /**
   * calculate zone matches and flag settings to determine whether this module should display to customers or not
   *
   */
  function update_status() {
    global $order, $db;

    if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYDOLLAR_ZONE > 0) ) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYDOLLAR_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }
  /**
   * JS validation which does error-checking of data-entry if this module is selected for use
   * (Number, Owner, and CVV Lengths)
   *
   * @return string
   */
  function javascript_validation() {
    
  }
  /**
   * Builds set of input fields for collecting PAYDOLLAR info
   *
   * @return array
   */
  function selection() {
   return array('id' => $this->code,
                 'module' => $this->title);
  }
  /**
   * Evaluates the Credit Card Type for PAYDOLLAR acceptance and the validity of the Credit Card Number & Expiration Date
   *
   */
  function pre_confirmation_check() {
   
 }
  /**
   * Display Credit Card Information on the Checkout Confirmation Page
   *
   * @return boolean
   */
  function confirmation() {
   
  }
  /**
   * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
   * This sends the data to the payment gateway for processing.
   * (These are hidden fields on the checkout confirmation page)
   *
   * @return string
   */
 function process_button() {
        global $_SERVER, $order, $db;

        $this->totalsum = $order->info['total'];	
		$this->orderRef = "";
		
		//just added - 10202011
		$redirectSection = '';
		if(MODULE_PAYMENT_PAYDOLLAR_REDIRECT != ''){
			$redirectSection = '<input type="hidden" name="redirect" value="'.MODULE_PAYMENT_PAYDOLLAR_REDIRECT.'"/>';			
		}
		
        $process_button_string = '<input type="hidden" name="merchantId" value="'.MODULE_PAYMENT_PAYDOLLAR_ID.'"/>
                                  <input type="hidden" name="amount" value="'. $this->totalsum .'" />
                                  <input type="hidden" name="orderRef" value="'. $this->orderRef .'"/>
                                  <input type="hidden" name="currCode" value="'. $this->getCurrencyCode() .'" />
                                  <input type="hidden" name="successUrl" value="'. zen_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL') .'"/>
                                  <input type="hidden" name="failUrl" value="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_status=failed', 'SSL') . '"/>
                                  <input type="hidden" name="cancelUrl" value="'. zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_status=canceled', 'SSL') .'"/>
                                  <input type="hidden" name="payType" value="N">
                                  <input type="hidden" name="actionUrl" value="'.MODULE_PAYMENT_PAYDOLLAR_HANDLER.'">
								  <input type="hidden" name="code" value="'. $this->code.'">
								  <input type="hidden" name="orderId" value="'. $this->getOrderId().'">
								  <input type="hidden" name="asiapay" value="true">
								  <input type="hidden" name="integrationType" value="clientPost">
                                  <input type="hidden" name="lang" value="'. $this->getLanguageCode() .'">
								  <input type="hidden" name="orderRefPrefix" value="'.MODULE_PAYMENT_PAYDOLLAR_ORDERREF .'"/>
                  '.$redirectSection.'
                  <input type="hidden" name="secureHashSecret" value="'. MODULE_PAYMENT_PAYDOLLAR_SHSKEY .'"/>
                  <input type="hidden" name="payType" value="'.$this->getPaymentType() .'"/>
                  <input type="hidden" name="threeDSTransType" value="'.$this->getTransactionType() .'"/>
                  <input type="hidden" name="threeDSChallengePreference" value="'.$this->getChallengePref() .'"/>'; //just added - 10202011//paytype added 20181220
								  
  return $process_button_string;
  }
  
  function getOrderId(){
  
   global $db;
  // find out the last order number generated for this customer account
	$orders_query = "SELECT * FROM " . TABLE_ORDERS . "
                 WHERE customers_id = :customersID
                 ORDER BY date_purchased DESC LIMIT 1";
				$orders_query = $db->bindVars($orders_query, ':customersID', $_SESSION['customer_id'], 'integer');
				$orders = $db->Execute($orders_query);
				$orders_id = $orders->fields['orders_id'];
				
    return $orders_id;
	
  }
  
  
    /*�344� - HKD
    �840� � USD
    �702� � SGD
    �156� � CNY (RMB)
    �392� � JPY
    �901� � TWD
    �036� � AUD
    �978� � EUR
    �826� � GBP
    �124� � CAD
    �608� � PHP
  */
  function getCurrencyCode(){

    switch (MODULE_PAYMENT_PAYDOLLAR_CURRENCY) {
            case 'Only HKD':  $cur = '344';
                    break;
            case 'Only USD':  $cur = '840';
                   break;
            case 'Only SGD':  $cur = '702';
                    break;
            case 'Only CNY':  $cur = '156';
                    break;
            case 'Only JPY':  $cur = '392';
                    break;
            case 'Only TWD':  $cur = '901';
                    break;
            case 'Only AUD':  $cur = '036';
                    break;
            case 'Only EUR':  $cur = '978';
                    break;
            case 'Only GBP':  $cur = '826';
                    break;
            case 'Only CAD':  $cur = '124';
                    break;
			case 'Only PHP':  $cur = '608';
                    break;
			case 'Only ZAR':  $cur = '710';
            		break;
            default:  $cur = '840';

    }

    return $cur;
  }

  /*
    The transaction type of the payment page i.e.
  */
  function getTransactionType(){
    switch (MODULE_PAYMENT_PAYDOLLAR_TRANSTYPE) {
         case 'Goods/ Service Purchase':  $type = '01';
                 break;
         case 'Check Acceptance':  $type = '03';
                break;
         case 'Account Funding':  $type = '10';
                break;
         case 'Quasi-Cash Transaction':  $type = '11';
                break;
         case 'Prepaid Activation and Load':  $type = '28';
                break;
         default:  $type = '01';
    
    }
    return $type;
  }

  /*
    The Challenge Pref of the payment page i.e.
  */
  function getChallengePref(){
    switch (MODULE_PAYMENT_PAYDOLLAR_CHALLENGEPREF) {
         case 'No preference':  $type = '01';
                 break;
         case 'No challenge requested*':  $type = '02';
                break;
         case 'Challenge requested (Merchant preference)':  $type = '03';
                break;
         case 'Challenge requested (Mandate)':  $type = '04';
                break;
         case 'No challenge requested (transactional risk analysis is already performed)*':  $type = '05';
                break;
         case 'No challenge requested (Data share only)*':  $type = '06';
                break;
         case 'No challenge requested (strong consumer authentication is already performed)*':  $type = '07';
                break;
         case 'No challenge requested (utilise whitelist exemption if no challenge required)*':  $type = '08';
                break;
         case 'Challenge requested (whitelist prompt requested if challenge required)':  $type = '09';
                break;
         default:  $type = '01';
    }
    return $type;
  }

  /*
    The language of the payment page i.e.
    �C� � Traditional Chinese
    �E� � English
    �X� � Simplified Chinese
    �K� � Korean
    �J� � Japanese
  */
  function getLanguageCode(){
       switch (MODULE_PAYMENT_PAYDOLLAR_LANGUAGE) {
            case 'Traditional Chinese':  $lang = 'C';
                    break;
            case 'English':  $lang = 'E';
                   break;
            case 'Simplified Chinese':  $lang = 'X';
                    break;
            case 'Korean':  $lang = 'K';
                    break;
            case 'Japanese':  $lang = 'J';
                    break;
            default:  $lang = 'E';

    }

    return $lang;
  }

  /*
    The payment type of the payment page i.e.
    �N� � N-Normal Payment (Sales)
    �H� � H-Hold Payment (Authorize only)
  */
  function getPaymentType(){
    switch (MODULE_PAYMENT_PAYDOLLAR_PAYTYPE) {
         case 'N-Normal Payment (Sales)':  $paytype = 'N';
                 break;
         case 'H-Hold Payment (Authorize only)':  $paytype = 'H';
                break;
         default:  $paytype = 'N';
    
 }

 return $paytype;  
}
  /**
   * Store the PAYDOLLAR info to the order
   *
   */
  function before_process() {
    
  }
  /**
   * Send the collected information via email to the store owner, storing outer digits and emailing middle digits
   *
   */
  function after_process() {

    
  }
function check_referrer($zf_domain) {

  }
  /**
   * Store additional order information
   *
   * @param int $zf_order_id
   */
  function admin_notification($zf_order_id) {

  }
  /**
   * Used to display error message details
   *
   * @return array
   */
  function get_error() {
    $error = array('title' => MODULE_PAYMENT_PAYDOLLAR_TEXT_ERROR,
                   'error' => stripslashes(urldecode($_GET['error'])));

    return $error;
  }
  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYDOLLAR_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }
  /**
   * Install the payment module and its configuration settings
   *
   */
 function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayDollar Module', 'MODULE_PAYMENT_PAYDOLLAR_STATUS', 'True', 'Do you want to accept Paydollar payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Paydollar ID', 'MODULE_PAYMENT_PAYDOLLAR_ID', '1', 'The merchant id used for the Paydollar service', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_PAYDOLLAR_CURRENCY', 'Only USD', 'Choose the currency/currencies you want to accept', '6', '0', 'zen_cfg_select_option(array(\'Only HKD\',\'Only USD\',\'Only SGD\',\'Only CNY\',\'Only JPY\',\'Only TWD\',\'Only AUD\',\'Only EUR\',\'Only GBP\',\'Only CAD\',\'Only PHP\',\'Only ZAR\'), ', now())");  
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYDOLLAR_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '134' , now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Language', 'MODULE_PAYMENT_PAYDOLLAR_LANGUAGE', 'English', 'Please choose the language page', '6', '0' , 'zen_cfg_select_option(array(\'Traditional Chinese\',\'English\',\'Simplified Chinese\',\'Korean\',\'Japanese\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYDOLLAR_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '136', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Paydollar server', 'MODULE_PAYMENT_PAYDOLLAR_HANDLER', 'https://test.pesopay.com/b2cDemo/eng/payment/payForm.jsp', 'Type the server that will handle the transaction. The default is:  <br/><br/><i>Production Site: <br/></i><code>https://www.paydollar.com/b2c2/eng/payment/payForm.jsp</code><br/><br/> <code>https://www.pesopay.com/b2c2/eng/payment/payForm.jsp</code><br/> <i>Test Site: </i><br/><code>https://test.paydollar.com/b2cDemo/eng/payment/payForm.jsp</code> <br/><br/> <code>https://test.pesopay.com/b2cDemo/eng/payment/payForm.jsp</code>', '6', '0', now())");
	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Order Reference Prefix', 'MODULE_PAYMENT_PAYDOLLAR_ORDERREF', 'PAYDOLLAR','The merchant reference id is needed to monitor the orders and the transactions in Paydollar.  The prefix here will be automatically added to the merchant reference id with a - dash should you wish to use this Order Reference Prefix.', '6', '0', now())");
  	//just added - 10182011
  $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Redirect Time', 'MODULE_PAYMENT_PAYDOLLAR_REDIRECT', '10', 'The number of seconds to auto-redirect back to merchant site from Paydollar Payment Success / Fail page. If empty, the default will be 10 seconds.', '6', '0', now())");
  
  //julius 2018-12-20
  //secure hash secret key
  $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secure Hash Secret Key', 'MODULE_PAYMENT_PAYDOLLAR_SHSKEY', '1', 'The Secure Hash Secret – Assigned by Paydollar to merchant', '6', '0', now())");
  $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Payment Type', 'MODULE_PAYMENT_PAYDOLLAR_PAYTYPE', 'N-Normal Payment (Sales)', 'Please choose the payment Type used for the Paydollar service', '6', '0' , 'zen_cfg_select_option(array(\'N-Normal Payment (Sales)\',\'H-Hold Payment (Authorize only)\'), ', now())");

  //3DS2.0
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Type', 'MODULE_PAYMENT_PAYDOLLAR_TRANSTYPE', 'Goods/ Service Purchase', 'Please choose the Transaction Type used for the Paydollar service', '6', '0' , 'zen_cfg_select_option(array(\'Goods/ Service Purchase\',\'Check Acceptance\',\'Account Funding\',\'Quasi-Cash Transaction\',\'Prepaid Activation and Load\'), ', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Challenge Type', 'MODULE_PAYMENT_PAYDOLLAR_CHALLENGEPREF', 'No preference', 'Please choose the Challenge preference used for the Paydollar service. * If \'No challenge requested\' options are selected, the chargeback liability shift to merchant.', '6', '0' , 'zen_cfg_select_option(array(\'No preference\',\'No challenge requested*\',\'Challenge requested (Merchant preference)\',\'Challenge requested (Mandate)\',\'No challenge requested (transactional risk analysis is already performed)*\',\'No challenge requested (Data share only)*\',\'No challenge requested (strong consumer authentication is already performed)*\',\'No challenge requested (utilise whitelist exemption if no challenge required)*\',\'Challenge requested (whitelist prompt requested if challenge required)\'), ', now())");

  // $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order,set_function, date_added) values ('Payment Type', 'MODULE_PAYMENT_PAYDOLLAR_PAYTYPE', 'N', 'The Payment Type used for the Paydollar service', '6','0', 'zen_cfg_select_option(array(\'N-Normal Payment (Sales)\',\'H-Hold Payment (Authorize only)\'),', now())");
}
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_PAYMENT\_PAYDOLLAR\_%'");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_PAYMENT_PAYDOLLAR_STATUS', 'MODULE_PAYMENT_PAYDOLLAR_ID', 'MODULE_PAYMENT_PAYDOLLAR_CURRENCY', 'MODULE_PAYMENT_PAYDOLLAR_SORT_ORDER', 'MODULE_PAYMENT_PAYDOLLAR_LANGUAGE', 'MODULE_PAYMENT_PAYDOLLAR_ORDER_STATUS_ID','MODULE_PAYMENT_PAYDOLLAR_HANDLER','MODULE_PAYMENT_PAYDOLLAR_ORDERREF', 'MODULE_PAYMENT_PAYDOLLAR_REDIRECT','MODULE_PAYMENT_PAYDOLLAR_SHSKEY','MODULE_PAYMENT_PAYDOLLAR_PAYTYPE','MODULE_PAYMENT_PAYDOLLAR_TRANSTYPE','MODULE_PAYMENT_PAYDOLLAR_CHALLENGEPREF');//,'MODULE_PAYMENT_PAYDOLLAR_TRANSTYPE','MODULE_PAYMENT_PAYDOLLAR_CHALLENGEPREF'
  }
}
?>