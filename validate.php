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
	// Ensure node ip is valid
	if ($octets[3] == 0 || $octets[3] == 255) {
		return false;
	}

	// Ensure network ip is valid
	if ($octets[0] == 127 || ($octets[0] > 224 && $octets[0] < 240)) {
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

function validate_ip4($ip_s, $gateway_s, $netmask_s) {

	// IP
	$ip = parse_ip4_address($ip_s);
	if (!$ip) {
		return false;
	}
	if (!validate_ip4_address($ip)) {
		return false;
	}

	// Gateway
	$gateway = parse_ip4_address($gateway_s);
	if (!$gateway) {
		return false;
	}
	if (!validate_ip4_address($gateway)) {
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

	// Gateway is on IP network
	$gateway_net = mask_ip4_address($gateway, $netmask);
	$ip_net = mask_ip4_address($ip, $netmask);
	for ($i = 0; $i < 4; $i++) {
		if ($ip_net[$i] != $gateway_net[$i]) {
			return false;
		}
	}

	// TODO: Gateway and IP are not Broadcast

	// Validated
	return true;
}

echo json_encode(validate_ip4("192.168.0.1", "192.168.4.254", "255.255.252.0"));

?>
