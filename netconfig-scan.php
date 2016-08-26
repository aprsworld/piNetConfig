<?php

function iwlist_parse_scan ($s) {
	$interfaces_strings = split("\n\n", $s);
	$ret = Array();

	foreach ($interfaces_strings as $interface_string) {
		$lines = split("\n", $interface_string);
		$iface = split(" ", $lines[0])[0];
		$ssid = NULL;
		$auth = NULL;
		preg_match('/ESSID:"([\w]+)"/', $interface_string, $matches);
		if (sizeof($matches) > 0) {
			$ssid = $matches[1];
		} else {
			continue;
		}
		preg_match('/Encryption key:on/', $interface_string, $matches);
		if (sizeof($matches) > 0) {
			$auth = true;
		} else {
			$auth = false;
		}
		// TODO: Encryption and Authentication info...

		$ret[$iface] = Array();
		$ret[$iface][$ssid] = Array();
		$ret[$iface][$ssid]['auth'] = $auth;
	}

	return $ret;
}

echo json_encode(iwlist_parse_scan(shell_exec('sudo iwlist scan 2> /dev/null')));

?>
