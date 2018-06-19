<?php
/**
 * Copyright (c) 2017, Art of WiFi
 *
 * This file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.md
 *
 */
/**
 * Controller configuration
 * ===============================
 * Copy this file to your working directory, rename it to config.php and update the section below with your UniFi
 * controller details and credentials
 */
$controlleruser     = 'admin'; // the user name for access to the UniFi Controller
$controllerpassword = '0t0w1+nc17'; // the password for access to the UniFi Controller
$controllerurl      = 'https://127.0.0.1:8443'; // full url to the UniFi Controller, eg. 'https://22.22.11.11:8443'
$controllerversion  = '5.6.37'; // the version of the Controller software, eg. '4.6.6' (must be at least 4.0.0)
/**
 * set to true (without quotes) to enable debug output to the browser and the PHP error log
 */
$debug = false;