<?php

// Parse iwconfig output
// TODO XXX
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
		$tmp = split("  ", substr($lines[0], 9));
		$iftype = trim($tmp[0]);
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
function ip_parse_link($s) {
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

		// Parse line 0
		$line0 = preg_replace("/^[0-9]+: +/", "", $lines[0]);
		$split0 = split("[ ]+", $line0);
		$interface = rtrim($split0[0], ":");
		$flags = $split0[1];
		$interface_config = Array();
		for ($i = 2; $i+1 < sizeof($split0); $i += 2) {
			$key = $split0[$i];
			$value = $split0[$i+1];
			$interface_config[$key] = $value;
		}

		// Parse line 2
		$split1 = split("[ ]+", $lines[1]);
		$interface_config["hwaddress"] = $split1[2];

		$ret[$interface] = $interface_config;
	}

	return $ret;
}

function ip_parse_route($s) {
	$routes_string = split("\n", $s);
	$ret = Array();

	// Parse each route
	foreach ($routes_string as $route_string) {
		$split = split("[ ]+", $route_string);
		if ($split[0] != "default") {
			continue;
		}
		$alias = $split[4];
		$tmp = split("[:.]", $alias, 2);
		$interface = $tmp[0];
		// XXX: $metric = $split[8];
		$gateway = $split[2];
		$ret[$interface] = Array();
		$ret[$interface][$alias] = Array();
		// TODO: inet6
		$ret[$interface][$alias]['inet'] = Array();
		$ret[$interface][$alias]['inet']['gateway'] = $gateway;
		//$ret[$interface][$alias]['inet']['metric'] = $metric;
	}

	return $ret;
}

function ip_parse_address($s) {
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
		if ($interface[strlen($interface)-1] ==  ":") {
			// Link info... skip
			continue;
		}
		$alias = $split0[sizeof($split0)-1];
		if ($alias == "") {
			$alias = $interface;
		}
		if ($split0[sizeof($split0)-2] == "secondary") {
			//$alias = $alias . '-secondary'; // XXX: Cludge
		}
		$family = $split0[1];
		$net = split("/", $split0[2], 2);
		$address = $net[0];
		$netmask = parse_ip4_address2string(parse_ip4_netsize2mask($net[1]));
		$alias_config = Array();
		$alias_config[$family] = Array();
		$alias_config[$family]["address"] = $address;
		$alias_config[$family]["netmask"] = $netmask;
		for ($i = 3; $i+1 < sizeof($split0); $i += 2) {
			$key = $split0[$i];
			$value = $split0[$i+1];
			$alias_config[$family][$key] = $value;
		}
		$interface_config = Array();
		$interface_config[$alias] = $alias_config;
		if (!array_key_exists($interface, $ret)) {
			$ret[$interface] = $interface_config;
		} else {
			$ret[$interface] = array_merge_recursive($interface_config, $ret[$interface]);
		}
	}

	return $ret;
}
?>
