<?php

if (file_exists('/usr/local/sbin/root-rw') && file_exists('/usr/local/sbin/root-ro')) {
	$root_rw = "sudo /usr/local/sbin/root-rw";
	$root_ro = "sudo /usr/local/sbin/root-ro";
} else {
	$root_rw = "echo yay > /dev/null";
	$root_ro = "echo yay > /dev/null";
}
$reboot = "sudo /sbin/shutdown -r -t 1 now";

require('netconfig.php');
require('validate.php');

function config_write ($config, $file) {
	$file = fopen($file, "w");
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
	return true;
}

# $config = interfaces_read("/etc/network/interfaces");
$config = json_decode(file_get_contents('php://input'), true);
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
}

echo json_encode($config);
?>
