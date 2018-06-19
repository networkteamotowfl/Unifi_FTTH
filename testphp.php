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
$unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl, $site_id, $controllerversion);
$set_debug_mode   = $unifi_connection->set_debug($debug);
$loginresults     = $unifi_connection->login();
$data             = $unifi_connection->list_usergroups();
$newApForSetup = file_get_contents('./hostnamesToCompareNew.txt');
$explodedApData = explode("\n",$newApForSetup);
print_r($data);
