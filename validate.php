<?php

function parse_ip4_address($s) {
	// Split string into octets
	$octets_s = split("\.", $s);
	$octets = Array();

	// More than four octets?
	if (sizeof($octets_s) != 4) {
		return false;
	}

	// Validate each octet.
	$i = 0;
	foreach ($octets_s as $octet_s) {

		// Validate it's actually an integer
		if (!preg_match("/^[0-9]([0-9]([0-9])?)?$/", $octet_s)) {
			return false;
		}

		// Convert to integer
		$int = intval($octet_s);

		// Validate it's an octet
		if ($int < 0 || $int > 255) {
			return false;
		}
		$octets[$i++] = $int;
	}

	// Valid
	return $octets;
}

function parse_ip4_netmask($octets) {

	// Test each octet
	$size = 0;
	$i = 0;
	foreach ($octets as $octet) {

		// Ensure no gaps between octets
		if ($i++ * 8 != $size && $octet != 0) {
			return false;
		}

		// Add this octet to size
		switch ($octet) {
			case 255:
				$size += 1;
			case 254:
				$size += 1;
			case 252:
				$size += 1;
			case 248:
				$size += 1;
			case 240:
				$size += 1;
			case 224:
				$size += 1;
			case 192:
				$size += 1;
			case 128:
				$size += 1;
			case 0:
				break;
			default:
				// This octet has a gap in it
				return false;
		}
	}

	// Valid
	return $size;
}

function parse_ip4_netsize2mask($size) {
	$octets = Array();
	$full = $size/8;
	$rem = $size%8;

	// Pad first octets with 255
	for ($i = 0; $i < $full; $i++) {
		$octets[$i] = 255;
	}

	// Insert important octet
	switch ($rem) {
		case 0:
			$octets[$i++] = 0;
			break;
		case 1:
			$octets[$i++] = 128;
			break;
		case 2:
			$octets[$i++] = 192;
			break;
		case 3:
			$octets[$i++] = 224;
			break;
		case 4:
			$octets[$i++] = 240;
			break;
		case 5:
			$octets[$i++] = 248;
			break;
		case 6:
			$octets[$i++] = 252;
			break;
		case 7:
			$octets[$i++] = 254;
			break;
		default:
			// Impossible...
			return false;
	}

	// Pad remaining octets with 0
	for (;$i < 4; $i++) {
		$octets[$i] = 0;
	}

	// All done
	return $octets;
}

function validate_ip4_address($octets) {
	// Ensure network ip is valid
	if ($octets[0] == 0 || $octets[0] == 127 || ($octets[0] > 224 && $octets[0] < 240)) {
		return false;
	}
	if ($octets[0] == 255 && $octets[1] == 255 && $octets[2] == 255 && $octets[3] == 255) {
		return false;
	}

	// Valid
	return true;
}

function mask_ip4_address($ip, $mask) {
	$ret = Array();
	for ($i = 0; $i < 4; $i++) {
		$ret[$i] = $ip[$i] & $mask[$i];
	}
	return $ret;
}

function compare_ip4_address($ip, $ip2) {
	for ($i = 0; $i < 4; $i++) {
		if ($ip[$i] != $ip2[$i]) {
			return false;
		}
	}
	return true;
}


function validate_ip4($ip_s, $netmask_s, $gateway_s) {

	// IP
	$ip = parse_ip4_address($ip_s);
	if (!$ip) {
		return false;
	}
	if (!validate_ip4_address($ip)) {
		return false;
	}

	// Netmask
	$netmask = parse_ip4_address($netmask_s);
	if (!$netmask) {
		return false;
	}
	$netsize = parse_ip4_netmask($netmask);
	if ($netsize < 8 || $netsize > 30) {
		return false;
	}

	// Network
	$ip_net = mask_ip4_address($ip, $netmask);

	// Gateway
	if ($gateway_s) {
		$gateway = parse_ip4_address($gateway_s);
		if (!$gateway) {
			return false;
		}
		if (!validate_ip4_address($gateway)) {
			return false;
		}

		// Gateway is on IP network
		$gateway_net = mask_ip4_address($gateway, $netmask);
		for ($i = 0; $i < 4; $i++) {
			if ($ip_net[$i] != $gateway_net[$i]) {
				return false;
			}
		}
	}

	// Gateway and IP are not Broadcast
	$broadcast = Array();
	for ($i = 0; $i < 4; $i++) {
		$broadcast[$i] = $ip_net[$i] | (~$netmask[$i] & 0xFF);
	}
	if (compare_ip4_address($broadcast, $ip)) {
		return false;
	}
	if ($gateway_s && compare_ip4_address($broadcast, $gateway)) {
		return false;
	}

	// General rules
	if ($netsize >= 24) {
		if ($ip[3] == 255 || $ip[3] == 0) {
			return false;
		}
		if ($gateway_s && ($gateway[3] == 255 || $gateway[3] == 0)) {
			return false;
		}
	}

	// Valid
	return true;
}


function parse_ip4_address2string($a) {
	return '' . $a[0] . '.' . $a[1] . '.' . $a[2] . '.' . $a[3];
}


function config_validate ($config) {

	foreach ($config as $iface => $ifconfig) {
		foreach ($ifconfig['protocol'] as $protocol => $pconfig) {
			if ($protocol != "inet") {
				echo 'Warning: Unsupported protocol for ' . $iface . '.\n';
				continue;
			}
			if (!array_key_exists('method', $pconfig)) {
				echo 'ERROR: No configuration method for ' . $iface . ' ' . $protocol . ' configuration!\n';
				return false;
			}
			$method = $pconfig['method'];
			if ($method != "static" && $method != "dhcp" && $method != "loopback") {
				echo "Warning: Unsupported configuration method " . $method . " for " . $iface . " " . $protocol . ".\n";
			}
			$address = NULL;
			$netmask = NULL;
			$gateway = NULL;
			foreach ($pconfig as $option => $value) {
				switch ($option) {
				case 'method':
					continue;
				case 'address':
					$address = $value;
					break;
				case 'netmask':
					$netmask = $value;
					break;
				case 'gateway':
					$gateway = $value;
					break;
				default:
					echo "Warning: Unsupported option " . $option . " specified for " . $method . " configuration of " . $iface . " " . $protocol . " configuration.\n";
					break;
				}
			}
			if ($address && $netmask) {
				if (!validate_ip4($address, $netmask, $gateway)) { 
					echo "Error: Invalid configuration for " . $iface . " " . $protocol . "!\n";
					return false;
				}
			} else if ($method == "static") {
				echo "Error: Invalid configuration for " . $iface . " " . $protocol . "!\n";
				return false;
			}
		}
	}

	return true;
}


//echo json_encode(validate_ip4("192.168.0.13", "192.168.0.1", "255.255.0.0"));

?>
