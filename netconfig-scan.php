<?php

function iwlist_parse_scan ($s) {
	$interfaces_strings = explode("\n\n", $s);
	$ret = Array();

	foreach ($interfaces_strings as $interface_string) {
		$lines = split("\n", $interface_string);
		$iface = split(" ", $lines[0])[0];

		$scans = explode("Cell", $interface_string);
		foreach ($scans as $scan) {
			$ssid = NULL;
			$auth = NULL;
			$key_mgmt = NULL;
			preg_match('/ESSID:"([\w]+)"/', $scan, $matches);
			if (sizeof($matches) > 0) {
				$ssid = $matches[1];
			} else {
				continue;
			}
			preg_match('/Encryption key:on/', $scan, $matches);
			if (sizeof($matches) > 0) {
				$auth = true;
			} else {
				$auth = false;
			}

			// XXX: TODO: Encryption and Authentication info...
			if ($auth) {
				if (preg_match('/WPA2/', $scan)) {
					$auth = 'WPA2';
				} else if (preg_match('/WPA/', $scan)) {
					$auth = 'WPA';
				} else {
					$auth = 'WEP';
				}
			}

			if (!array_key_exists($iface, $ret)) {
				$ret[$iface] = Array();
			}
			$ret[$iface][$ssid] = Array();
			$ret[$iface][$ssid]['auth'] = $auth;
			if ($key_mgmt) {
				$ret[$iface][$ssid]['key_mgmt'] = $key_mgmt;
			}
		}
	}

	return $ret;
}

echo json_encode(iwlist_parse_scan(shell_exec('sudo iwlist scan 2> /dev/null')));

?>
