<?php

require('netconfig.php');

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
config_write($config);

?>
