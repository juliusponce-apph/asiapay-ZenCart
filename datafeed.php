<?php
include_once './constants.php';

//include('includes/configure.php');		//may be used as a replacement for constants.php should you want to use the original 'configure.php' of Zen-Cart as your file for constants; 
include('includes/application_top.php');

/*
To check if there are parameters passed:
	if($_POST){
	print_r($_POST);
	}else{
	echo "There are no parameters passed";
	}

*/

//POSTed variables by the gateway
$src = $_POST['src'];
$prc = $_POST['prc'];
$ord = $_POST['Ord'];
$holder = $_POST['Holder'];
$successCode = $_POST['successcode'];
$ref = $_POST['Ref'];
$payRef = $_POST['PayRef'];
$amt = $_POST['Amt'];
$cur = $_POST['Cur'];
$remark = $_POST['remark'];
$authId = $_POST['AuthId'];
$eci = $_POST['eci'];
$payerAuth = $_POST['payerAuth'];
$sourceIp = $_POST['sourceIp'];
$ipCountry = $_POST['ipCountry'];

//confirmation sent to the gateway to explain that the variables have been sent
echo "OK! " . "orderRef: ". $ref . "<br />";

//connect to DB
$link = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
if (!$link) {
   	die('Could not connect: ' . mysql_error());
}	
@mysql_select_db(DB_DATABASE) or die( "Unable to select database");

//explode reference number and get the value only
$refFlag = preg_match("/-/", $ref);	
if ($refFlag == 1){
	$orderId = explode("-",$ref);
	$orderNumber = ltrim($orderId[1],"0");
}else{
	$orderNumber = ltrim($ref,"0");
}
echo $orderNumber."<br>";

//query final amount and currency type from the DB
$value_query = "SELECT order_total, currency FROM orders WHERE orders_id ='$orderNumber'";
$value_result = mysql_query($value_query) or die ("Unable to query"); 
$value_item= mysql_fetch_assoc($value_result);
$checkAmt = $value_item['order_total'];
$checkCur = $value_item['currency'];

//convert taken value from the DB into numerical ISO code for currency
if ($checkCur == "USD")  //Ammerican Dollar
	$checkCur = 840;
else if ($checkCur == "HKD")  //Hong Kong Dollar
	$checkCur = 344;
else if ($checkCur == "PHP")
	$checkCur = 608;
else if ($checkCur == "ZAR")
	$checkCur = 710;

//***You can add code for currencies other than USD and HKD

//***Here are some code for other currencies :

//****************************************

//	else if ($checkCur == "SGD")  //Singapore Dollar

//	$checkCur = 702;

//	else if ($checkCur == "CNY(RMB)")  //Chinese Renminbi Yuan

//	$checkCur = 156;

//	else if ($checkCur == "JPY")  //Japanese Yen

//	$checkCur = 392;

//	else if ($checkCur == "TWD")  //New Taiwan Dollar

//	$checkCur = 901;

//****************************************

/*
//For searching the database if the status name is not available inside the 'orders_status' table.  If it is not available, the codes will attempt to add the status names into the table.
$status_query = "SELECT orders_status_id, orders_status_name FROM orders_status WHERE orders_status_name='".SUCCESSSTATUSNAME."' OR orders_status_name='".FAILSTATUSNAME."'";
$status_result = mysql_query($status_query);
if (!status_result){
	$message  = 'Invalid query: ' . mysql_error() . "\n";
	$message .= 'Whole query: ' . $status_query;
	die($message);
}else{
	$counter = 0;
	
	while ($status_item = mysql_fetch_assoc($status_result)) {
		$orders_status_id = $status_item['orders_status_id'];
		$orders_status_name = $status_item['orders_status_name'];
		echo $orders_status_id;
		
		if (($orders_status_id == '' || $orders_status_id == null) && ($orders_status_name == '' || $orders_status_name == null)){
			$max_orders_status_id_query = "SELECT MAX(orders_status_id) FROM orders_status";
			$max_orders_status_id_result = mysql_query($max_orders_status_id_query) or die('Unable to query max order status id');
			$max_orders_status_id = mysql_result($max_orders_status_id_result, 0);
			$max_orders_status_id += 1;
			echo $max_orders_status_id;
			
			if ($counter == 0){
				$status_query = "INSERT INTO orders_status VALUES('".$max_orders_status_id."','1','".SUCCESSSTATUSNAME."')";
				$status_query_result = mysql_query($status_query) or die('Unable to insert success status name'); 
			}else{	
				$status_query = "INSERT INTO orders_status VALUES('".$max_orders_status_id."','1','".FAILSTATUSNAME."')";
				$status_query_result = mysql_query($status_query) or die('Unable to insert fail status name'); 
			}
			$counter += 1;
		}
		
	}
}
*/

//determination of successful or failed transaction from the POSTed values from the gateway
if ($successCode == 0 && $prc==0 && $src==0){
	echo $checkAmt . " " .$amt . "<br />";
	echo $checkCur . " " .$cur . "<br />";
	//determination of correct amount and currency with the one from the DB
	if ($checkAmt == $amt && $checkCur == $cur){

		$query = array();
		$answer = array();
		
		$query[0]="UPDATE orders SET orders_status =(SELECT orders_status_id FROM orders_status WHERE orders_status_name = '".SUCCESSSTATUSNAME."') WHERE orders_id='$orderNumber'"; 
		$answer[0] = mysql_query($query[0]);
		if (!$answer[0]){
			$message  = 'Invalid query: ' . mysql_error() . "\n";
		    $message .= 'Whole query: ' . $query[0];
		    die($message);
		}else{
			echo "Record Updated - Orders Status <br />";
		}
	
		$query[1]="UPDATE orders_status_history SET orders_status_id = (SELECT orders_status_id from orders_status WHERE orders_status_name = '".SUCCESSSTATUSNAME."') WHERE orders_id='$orderNumber'"; 
		$answer[1] = mysql_query($query[1]);	
		if (!$answer[1]){
			$message  = 'Invalid query: ' . mysql_error() . "\n";
		    $message .= 'Whole query: ' . $query[1];
		    die($message);
		}else{
			echo "Record Updated - Order Status History <br />";
		}
		
		$query[2]="SELECT customers_firstname, customers_lastname, customers_email_address FROM customers WHERE customers_id = (SELECT customers_id FROM orders WHERE orders_id = '$orderNumber')";
		$answer[2] = mysql_query($query[2]) or die ("Unable to query");	
		$query_assoc= mysql_fetch_assoc($answer[2]);
		$customersFirstName = $query_assoc['customers_firstname'];
		$customersLastName = $query_assoc['customers_lastname'];
		$customersEmail = $query_assoc['customers_email_address'];
		
		//to email the customer of the successful transaction
		zen_mail($customersFirstName. ' ' . $customersLastName, $customersEmail, EMAIL_SUBJECT_CC_MSG, EMAIL_SUCCESS_CC_MSG . ' Order number: '.$ref . " Link: " . ORDERLINK . $orderNumber, STORE_OWNER, EMAIL_FROM, array('EMAIL_MESSAGE_HTML' => nl2br(EMAIL_TEXT_CC_MSG)),'ccnotice');
	}else{
		echo "(POSTed Amount != DB Amount) || (POSTed Currency != DB Currency)!!!  DB will not update status.<br />";
	}
	
}else{
	$query = array();
	$answer = array();
	
	$query[0]="UPDATE orders SET orders_status=(SELECT orders_status_id FROM orders_status WHERE orders_status_name = '".FAILSTATUSNAME."') WHERE orders_id='$orderNumber'"; 
	$answer[0] = mysql_query($query[0]);
	if (!$answer[0]){
		$message  = 'Invalid query: ' . mysql_error() . "\n";
	    $message .= 'Whole query: ' . $query[0];
	    die($message);
	}else{
		echo "Record Updated - Orders Status <br />";
	}
	
	$query[1]="UPDATE orders_status_history SET orders_status_id = (SELECT orders_status_id FROM orders_status WHERE orders_status_name = '".FAILSTATUSNAME."') WHERE orders_id='$orderNumber'"; 
	$answer[1] = mysql_query($query[1]);	
	if (!$answer[1]){
		$message  = 'Invalid query: ' . mysql_error() . "\n";
	    $message .= 'Whole query: ' . $query[1];
	    die($message);
	}else{
		echo "Record Updated - Order Status History <br />";
	}
	
	$query[2]="SELECT customers_firstname, customers_lastname, customers_email_address FROM customers WHERE customers_id = (SELECT customers_id FROM orders WHERE orders_id = '$orderNumber')";
	$answer[2] = mysql_query($query[2]) or die ("Unable to query");	
	$query_assoc= mysql_fetch_assoc($answer[2]);
	$customersFirstName = $query_assoc['customers_firstname'];
	$customersLastName = $query_assoc['customers_lastname'];
	$customersEmail = $query_assoc['customers_email_address'];
	
	//to email the customer of the failed transaction
	zen_mail($customersFirstName. ' ' . $customersLastName, $customersEmail, EMAIL_SUBJECT_CC_MSG, EMAIL_FAIL_CC_MSG . ' Order number: '.$ref . " Link: " . ORDERLINK . $orderNumber, STORE_OWNER, EMAIL_FROM, array('EMAIL_MESSAGE_HTML' => nl2br(EMAIL_TEXT_CC_MSG)),'ccnotice');
	
	echo "FAILED / REJECTED Transaction! <br />";
}

mysql_close($link);
?>