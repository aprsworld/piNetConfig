<?php

require('netconfig.php');
require('validate.php');

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

function config_write ($config) {
	$file = fopen("/tmp/interfaces.txt", "w");
	if (!$file) {
		return false;
	}

	foreach ($config as $iface => $ifconfig) {
		if (array_key_exists("auto", $ifconfig) && $ifconfig['auto']) {
			fwrite($file, "auto " . $iface . "\n");
		}
		if (array_key_exists("allow", $ifconfig)) {
			if (is_array($ifconfig['allow'])) {
				foreach ($ifconfig['allow'] as $allow) {
					fwrite($file, "allow-" . $allow . " " . $iface . "\n");
				}
			} else if (is_string($ifconfig['allow'])) {
				fwrite($file, "allow-" . $ifconfig['allow'] . " " . $iface . "\n");
			}
		}
		foreach ($ifconfig['protocol'] as $protocol => $pconfig) {
			fwrite($file, "iface " . $iface . " " . $protocol . " " . $pconfig['method'] . "\n");
			foreach ($pconfig as $key => $value) {
				if ($key == "method") {
					continue;
				}
				if (!is_array($value)) {
					fwrite($file, "\t" . $key . " " . $value . "\n");
					continue;
				}
				foreach ($value as $v) {
					fwrite($file, "\t" . $key . " " . $v . "\n");
				}
			}
		}
		fwrite($file, "\n");
	}

	fclose($file);
}

$config = interfaces_read("/etc/network/interfaces");
if (config_validate($config)) {
	config_write($config);
}

?>
