"# Unifi_FTTH" 


Updates Out_List.csv from powershell script in FTTH_Tools (export DCHP list and format to CSV file)
Run updateFileForComparison: Updates IPs in fileForComparison to match real time DHCP leases
Run unifiAdoption:
	Adopts all adoptable unifis (python calls Unifi_PHP_ReturnNew.php, returns list of IPs and MACs of APsback to python)
	Opens OutList.csv (current list of all known routers, found from DHCP leases)
	Opens fileForComparison (list of previously known routers from last run)
	Compare files, finds if newly adopted APs are new installs or replacements, adds to respective lists for setup (including different variables for new or replacement)
		Comparison is done by parsing fileForComparison, seeing if adopted AP IP is in list. If not in list, knows it is new. If in list, then knows it is replacement
	Python calls Unifi_PHP_AdoptReplacements.php
	Unifi_PHP_AdoptReplacements sets up all newly adopted APs
		Knows which replacement AP belongs to which customer by comparing hostname in replacment file to the WLAN_Groups UNIFI list
		Sets up new APs to correct customer by IP address
