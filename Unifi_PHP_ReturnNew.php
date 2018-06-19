<?php
/**
 * PHP API usage example
 *
 * contributed by: Art of WiFi
 * description: example basic PHP script to pull current alarms from the UniFi controller and output in json format
 */
require_once('vendor/autoload.php');
require_once('config.php');
$site_id = 'default';
$ng_tx_power_mode = 'high';
$ng_channel = "auto";
$na_tx_power_mode = 'high';
$na_channel = "auto";
$unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl, $site_id, $controllerversion);
$set_debug_mode   = $unifi_connection->set_debug($debug);
$loginresults     = $unifi_connection->login();
$data             = $unifi_connection->list_devices();
$adoptable = array();
$wanIP = array();


foreach($data as $item) {
	if ($item->adopted) {
}
	else {
		array_push($adoptable, $item->mac);
}
}
foreach($adoptable as $currentMac) {
	$unifi_connection->adopt_device($currentMac);
}

if (sizeof($adoptable) !=0) {
	sleep(150);
}

foreach($adoptable as $currentMac) {
	$informationForParsing = $unifi_connection->list_devices($device_mac = $currentMac);
	echo $informationForParsing[0]->connect_request_ip;
	echo "\n";
	echo $informationForParsing[0]->mac;
	echo "\n";
}
