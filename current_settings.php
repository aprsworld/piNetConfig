<?php

// Parse iwconfig output
function iwconfig_parse($s) {
	$interfaces_string = split("\n\n", $s);
	$ret = Array();

	// Parse each interface
	foreach ($interfaces_string as $interface_string) { 
		// Empty interface
		if (trim($interface_string) == "") {
			continue;
		}

		// Parse each line
		$lines = split("\n", $interface_string);
		$ifname = trim(substr($lines[0], 0, 8));
		$iftype = trim(split("  ", substr($lines[0], 9))[0]);
		$ifsettings = Array();

		// Parse settings/values
		foreach ($lines as $line) {
			$line2 = trim(substr($line, 9));
			$settings = split("  ", $line2);
			foreach ($settings as $setting) {

				// Split into key value pairs
				$kv_pair = split(":", $setting, 2);
				if (sizeof($kv_pair) == 2) {
					$ifsettings[$kv_pair[0]] = trim($kv_pair[1]);
				} else {
					$kv_pair = split("=", $setting, 2);
					if (sizeof($kv_pair) == 2) {
						$ifsettings[$kv_pair[0]] = trim($kv_pair[1]);
					} else {
						// Hack to not have type as setting
						if ($kv_pair[0] == $iftype) {
							continue;
						}
						// Ignore empty stuffs
						if ($kv_pair[0] == "") {
							continue;
						}
						$ifsettings[$kv_pair[0]] = NULL;
					}
				}
			}
		}

		// Create interface structure
		$wireless = Array();
		$wireless["type"] = $iftype;
		$wireless["settings"] = $ifsettings;
		$ret[$ifname] = Array();
		$ret[$ifname]["wireless"] = $wireless;
	}

	// All done
	return $ret;
};

// ip parse
function ip_parse($s) {
	$interfaces_string = split("\n", $s);
	$ret = Array();

	// Parse each interface
	foreach ($interfaces_string as $interface_string) { 
		// Empty interface
		if (trim($interface_string) == "") {
			continue;
		}

		// Split into lines
		$lines = split("\\\\", $interface_string);

		// Parse line 0 with address info
		$line0 = preg_replace("/^[0-9]+: +/", "", $lines[0]);
		$split0 = split("[ ]+", $line0);
		$interface = $split0[0];
		if (!array_key_exists($interface, $ret)) {
			$ret[$interface] = Array();
		}
		$family = $split0[1];
		$ret[$interface][$family] = Array();
		$address = $split0[2];
		$ret[$interface][$family]["address"] = $address;
		for ($i = 3; $i+1 < sizeof($split0); $i += 2) {
			$key = $split0[$i];
			$value = $split0[$i+1];
			$ret[$interface][$family][$key] = $value;
		}
	}

	return $ret;
}

	$iwconfig_output = shell_exec("/sbin/iwconfig 2> /dev/null");
	if ($iwconfig_output != NULL) {
		$iwconfig = iwconfig_parse($iwconfig_output);
	}
	
	$ip_output = shell_exec("/sbin/ip -o addr 2> /dev/null");
	if ($ip_output == NULL) {
		exit(-1);
	}
	$ip = ip_parse($ip_output);

	$config = array_merge_recursive($ip, $iwconfig);
	echo json_encode($config);
?>
