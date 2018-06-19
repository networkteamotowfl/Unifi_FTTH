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
foreach($data as $item) {
	if ($item->adopted) {
}
	else {
		array_push($adoptable, $item->mac);
}
}
//echo json_encode($adoptable, JSON_PRETTY_PRINT);


// Read JSON file
$json = file_get_contents('./pythonWiFi.txt');

//Decode JSON
$json_data = json_decode($json,true);
//print_r ($json_data['test123']);
$adoptable_size = sizeof($adoptable);

$key_array = [];

foreach (array_keys($json_data) as $i){
	array_push($key_array, $i);
}


$currentMac = "";
for ($i = 0; $i < $adoptable_size; $i++)
{
	for ($j = 0; $j < sizeof($key_array); $j++) 
	{
		$values = array_values($json_data[$key_array[$j]]);
		if ($adoptable[$i] == $values[0]) 
		{
			$currentMac = $adoptable[$i];
			$APname = $values[1];
			$wifiName = $values[2];
			$pass = $values[3];
			//mac operations here
			echo $currentMac;
			echo "\n";
			echo $APname;
			echo "\n";
			echo $wifiName;
			echo "\n";
			echo $pass;
			echo "\n";
			//Adopt Device
			$unifi_connection->adopt_device($currentMac);
			sleep(120);
			//Find DeviceID
			$deviceID = $unifi_connection->list_devices($currentMac)[0]->_id;
			//Rename Device
			$unifi_connection->rename_ap($deviceID, $APname);
			//create wlangroup
			$unifi_connection->create_wlangroup($APname);
			//find wlangroup ID
			$wlanInfo = $unifi_connection->list_wlan_groups();
			$wlanID = "";
			for ($i = 0; $i < sizeof($wlanInfo); $i++)
			{
				if ($wlanInfo[$i]->name == $APname)
				{
					$wlanID = $wlanInfo[$i]->_id;
				}
			}
			
			//create wifi network
			$unifi_connection->create_wlan($wifiName, $pass, "58dc08b221ec1194fd5331dd", $wlanID);
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
			break;
		}
	}
}