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
$data             = $unifi_connection->list_wlan_groups();
$hostnames = file_get_contents('./hostnamesToCompareReplacement.txt');
$explodedHostnames = explode("\n",$hostnames);
$newApForSetup = file_get_contents('./hostnamesToCompareNew.txt');
$explodedApData = explode("\n",$newApForSetup);
$wlanGroups = [];

//$deviceID = $unifi_connection->list_devices($currentMac)[0]->_id;

foreach (array_keys($data) as $i){
	array_push($wlanGroups, $data[$i]->name);
}

//find replacements
$x = 0;
print_r($explodedHostnames);
echo "\n";
	

foreach ($explodedHostnames as $i) {
	$i = substr_replace($i, "", -1);
	$explodedHostnames[$x] = substr_replace($explodedHostnames[$x], "", -1);
	//replacement found
	if (in_array($i, $wlanGroups)) {
		//find wlangroup ID
		$wlanInfo = $unifi_connection->list_wlan_groups();
		$wlanID = "";
		
		for ($j = 0; $j < sizeof($wlanInfo); $j++)
		{
			if ($wlanInfo[$j]->name == $i)
			{
				echo "this is a matched i";
				echo "\n";
				echo $i;
				echo "\n";
				$wlanID = $wlanInfo[$j]->_id;
			}
		}
		$mac = $explodedHostnames[array_search($i, $explodedHostnames) + 1];
		echo $mac;
		echo "\n";
		//echo "----";
		$deviceID = $unifi_connection->list_devices($mac)[0]->_id;
		$unifi_connection->set_ap_wlangroup("ng", $deviceID, $wlanID);
		$unifi_connection->set_ap_wlangroup("na", $deviceID, $wlanID);
		$unifi_connection->rename_ap($deviceID, $i);
		$data_for_update = $unifi_connection->list_devices($mac);
		$radio_table      = $data_for_update[0]->radio_table;
		$device_id        = $data_for_update[0]->device_id;
		foreach($radio_table as $radio){
				if($radio->radio === 'ng'){
				$radio->tx_power_mode = $ng_tx_power_mode;
				$radio->channel = $ng_channel;
			}

			if($radio->radio === 'na')
		{
				$radio->tx_power_mode = $na_tx_power_mode;
				$radio->channel = $na_channel;
			}
		}
		$unifi_connection->set_device_settings_base($device_id, ['radio_table' => $radio_table]);
		$unifi_connection->upgrade_device($mac);
		}
	$x+=1;
	
	
}
if (sizeof($explodedApData) == 1){
	array_pop($explodedApData);
}

for ($i = 0; $i < (sizeof($explodedApData)-1); $i+=4) {
			$currentMac = $explodedApData[$i+1];
			$currentMac = substr_replace($currentMac, "", -1);
			$APname = $explodedApData[$i];
			$APname = substr_replace($APname, "", -1);
			$wifiName = $explodedApData[$i+3];
			$wifiName = substr_replace($wifiName, "", -1);
			$pass = $explodedApData[$i+2];
			$pass = substr_replace($pass, "", -1);
			//mac operations here
			echo $currentMac;
			echo "\n";
			echo $APname;
			echo "\n";
			echo $wifiName;
			echo "\n";
			echo $pass;
			echo "\n";
			//Find DeviceID
			$deviceID = $unifi_connection->list_devices($currentMac)[0]->_id;
			//Rename Device
			$unifi_connection->rename_ap($deviceID, $APname);
			//create wlangroup
			$unifi_connection->create_wlangroup($APname);
			//find wlangroup ID
			$wlanInfo = $unifi_connection->list_wlan_groups();
			$wlanID = "";
			for ($j = 0; $j < sizeof($wlanInfo); $j++)
			{
				if ($wlanInfo[$j]->name == $APname)
				{
					$wlanID = $wlanInfo[$j]->_id;
				}
			}
			//create wifi network
			$unifi_connection->create_wlan($wifiName, $pass, "5af057a8d64212cdc74e7e4c", $wlanID);
			//set AP wifi settings, for 2 and 5 channels
			$unifi_connection->set_ap_wlangroup("ng", $deviceID, $wlanID);
			$unifi_connection->set_ap_wlangroup("na", $deviceID, $wlanID);
			//update radio settings

			$data_for_update = $unifi_connection->list_devices($currentMac);
			$radio_table      = $data_for_update[0]->radio_table;
			$device_id        = $data_for_update[0]->device_id;
			foreach($radio_table as $radio){
    				if($radio->radio === 'ng'){
        			$radio->tx_power_mode = $ng_tx_power_mode;
        			$radio->channel = $ng_channel;
    			}

    			if($radio->radio === 'na')
			{
        			$radio->tx_power_mode = $na_tx_power_mode;
        			$radio->channel = $na_channel;
    			}
			}
			$unifi_connection->set_device_settings_base($device_id, ['radio_table' => $radio_table]);
			$unifi_connection->upgrade_device($currentMac);
}