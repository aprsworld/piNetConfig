<?php

function config_validate ($config) {

	if (!$config) {
		return false;
	}

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

function config_write ($config, $file) {
	$file = fopen($file, "w");
	if (!$file) {
		return false;
	}

	foreach ($config as $iface => $ifconfig) {
		if ($iface == "system") {
			continue;
		}
		if ($iface == "source" || $iface == "source-directory") {
			if (is_array($ifconfig)) {
				foreach ($ifconfig as $source) {
					fwrite($file, $iface . $source . "\n");
				}
			} else {
				fwrite($file, $iface . $ifconfig . "\n");
			}
			continue;
		}
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
	return true;
}


function netconfig_write($config) {
	
	if (file_exists('/usr/local/sbin/root-rw') && file_exists('/usr/local/sbin/root-ro')) {
		$root_rw = "sudo /usr/local/sbin/root-rw";
		$root_ro = "sudo /usr/local/sbin/root-ro";
	} else {
		$root_rw = "echo yay > /dev/null";
		$root_ro = "echo yay > /dev/null";
	}
	$reboot = "nohup sudo /sbin/shutdown -r -t 10 now > /dev/null 2>&1 &";
	
	if (config_validate($config)) {
		if (!config_write($config, "/tmp/interfaces")) {
			echo "ERROR: Couldn't write temporary config file!\n";
			return 1;
		}
		system('sudo /bin/chmod 644 /tmp/interfaces', $ret);
		if ($ret) {
			echo "ERROR: Couldn't set permissions to temporary config file!\n";
			return 1;
		}
		system('sudo /bin/chown root:root /tmp/interfaces', $ret);
		if ($ret) {
			echo "ERROR: Couldn't set temporary config file owner to root!\n";
			return 1;
		}
		system($root_rw, $ret);
		if ($ret) {
			echo "ERROR: Couldn't make filesystem writable!\n";
			return 1;
		}
		system('sudo mv /tmp/interfaces /etc/network/interfaces', $ret);
		if ($ret) {
			system($root_ro);
			echo "ERROR: Couldn't move temporary config file to perminent location!\n";
			return 1;
		}
		system($root_ro);
		system($reboot);
	} else {
		echo "Invalid Config!";
		return false;
	}
	
	echo json_encode($config);
}
?>
