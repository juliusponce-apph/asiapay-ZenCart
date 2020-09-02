<?php


function getCustomerBillAddress($orders){
	$arrBData = array();

	$billCountry = getCountryCallAPI($orders->fields['billing_country']);


	if(count($billCountry)>0)
  		$countryBNumCode = $billCountry[0]->numericCode;

	$arrBData['threeDSBillingLine1'] = $orders->fields['billing_street_address'];
	$arrBData['threeDSBillingLine2'] = $orders->fields['billing_suburb'];
	$arrBData['threeDSBillingCity'] = $orders->fields['billing_city'];
	$arrBData['threeDSBillingCountryCode'] = $countryBNumCode;
	$arrBData['threeDSBillingPostalCode'] = $orders->fields['billing_postcode'];
	// $arrBData['threeDSBillingState'] = $order->fields['billing_state'];

	return $arrBData;
	
}

function getCustomerShipAddress($orders){
	$arrBData = array();

	$shipCountry = getCountryCallAPI($orders->fields['delivery_country']);

	// print_r($shipCountry);

	if(count($shipCountry)>0)
  		$countrySNumCode = $shipCountry[0]->numericCode;


	$arrBData['threeDSShippingLine1'] = $orders->fields['delivery_street_address'];
	$arrBData['threeDSShippingLine2'] = $orders->fields['delivery_suburb'];
	$arrBData['threeDSShippingCity'] = $orders->fields['delivery_city'];
	$arrBData['threeDSShippingCountryCode'] = $countrySNumCode;
	$arrBData['threeDSShippingPostalCode'] = $orders->fields['delivery_postcode'];
	$arrBData['threeDSDeliveryEmail'] = $orders->fields['customers_email_address'];
	// $arrBData['threeDSShippingState'] = $order->fields['delivery_state'];



	return $arrBData;

}

  function getCountryCallAPI($countryName){
    $method = "GET";
    $url = "https://restcountries.eu/rest/v2/name/$countryName";
    
    // $data = array('codes'=>$countryCode);
    $data = false;

    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return json_decode($result);

}

function isSameBillShipAddress($b,$s){


    $cnt = 0;

    // if($b['state'] == $s['state'])$cnt++;
    if($b['threeDSBillingLine1'] == $s['threeDSShippingLine1'])$cnt++;
    if($b['city'] == $s['city'])$cnt++;
    if($b['street_address'] == $s['street_address'])$cnt++;
    if($b['suburb'] == $s['suburb'])$cnt++;
    if($b['postcode'] == $s['postcode'])$cnt++;


    if($cnt==5)return "T";
    else return "F";

  }

function getCustomerDetl($orders){
	$arrData = array();

	$country = getCountryCallAPI($orders->fields['delivery_country']);

	if(count($country)>0)
  		$phoneCountryCode = $country[0]->callingCodes[0];

	$arrData['threeDSCustomerEmail'] = $orders->fields['customers_email_address'];
	$arrData['threeDSMobilePhoneNumber'] =$arrData['threeDSHomePhoneNumber'] = $arrData['threeDSWorkPhoneNumber']= preg_replace('/\D/', '',$orders->fields['customers_telephone']);
	$arrData['threeDSWorkPhoneCountryCode'] = $arrData['threeDSHomePhoneCountryCode'] = $arrData['threeDSMobilePhoneCountryCode'] = $phoneCountryCode;

	if($orders->fields['customers_id'] > 0){
		$arrData['threeDSAcctAuthMethod'] = "02";
	}else{
		$arrData['threeDSAcctAuthMethod'] = "01";
	}


	return $arrData;
}


function getCustomerAcctInfo($acct_info){

	$dte_add = date('Ymd' , strtotime($acct_info->fields['customers_info_date_account_created']));
	$dte_upd = date('Ymd' , strtotime($acct_info->fields['customers_info_date_account_last_modified']));

	$dteAdd_diff = getDateDiff($dte_add);
	$dteUpd_diff = getDateDiff($dte_upd);

	$dteAddAge = getAcctAgeInd($dteAdd_diff);
	$dteUpdAge = getAcctAgeInd($dteUpd_diff,TRUE);

	
	$arrData['threeDSAcctCreateDate'] = $dte_add;
	$arrData['threeDSAcctLastChangeDate'] = $dte_upd;

	$arrData['threeDSAcctAgeInd'] = $dteAddAge;
	$arrData['threeDSAcctLastChangeInd'] = $dteUpdAge;

	return $arrData;

}

function getDateDiff($d){
    		$datenow = date('Ymd');
			$dt1 = new \DateTime($datenow);
			$dt2 = new \DateTime($d);
			$interval = $dt1->diff($dt2)->format('%a');
			return $interval;
    }

function getAcctAgeInd($d, $isUpDate = FALSE){
    	switch ($d) {
    		case 0:
    			# code...
    			$ret = "02";
    			if($isUpDate)$ret = "01";
    			break;
    		case $d<30:
    			# code...
    			$ret = "03";
    			if($isUpDate)$ret = "02";
    			break;
    		case $d>30 && $d<60:
    			# code...
    			$ret = "04";
    			if($isUpDate)$ret = "03";
    			break;
    		case $d>60:
    			$ret = "05"	;
    			if($isUpDate)$ret = "04";
				break;	
    		default:
    			# code...
    			break;
    	}
    	return $ret;

    }


$arrBillData = getCustomerBillAddress($orders);
$arrShipData = getCustomerShipAddress($orders);
$arrUserData = getCustomerDetl($orders);
$arrAcctInfo = getCustomerAcctInfo($acct_info);
$isSameAddress = isSameBillShipAddress($arrBillData,$arrShipData);

$shipDetl = $isSameAddress ? '01' : '03';

// print_r($arrBillData);
// print_r($arrShipData);
// print_r($arrUserData);
// exit;
// echo $orders->fields['customers_email_address'];
$arrThreeDSData = array();

$arrThreeDSData['threeDSIsAddrMatch'] = $isSameAddress;
$arrThreeDSData['threeDSShippingDetails'] = $shipDetl;

$arrThreeDSData = array_merge($arrThreeDSData,$arrBillData,$arrShipData,$arrUserData,$arrAcctInfo);


?>