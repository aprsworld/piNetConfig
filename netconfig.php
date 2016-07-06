<?php

function interfaces_scan($interfaces, $filename) {
	$interfaces_f = file($filename);
	$lines = preg_grep("/^[\s]*iface[\s]+([a-zA-Z0-9]+)[\s]+/", $interfaces_f);
	foreach ($lines as $line) {
		$split = split(" ", $line);
		if (sizeof($split) < 2) {
			continue;
		}
		array_push($interfaces, $split[1]);
	}
	return array_unique($interfaces);
}

function config_add(&$config, $iface, $option, $value) {
	// Iface doesn't exist
	if ($config[$iface] == NULL) {
		$config[$iface] = Array();
	}

	// Option already exists
	if (array_key_exists($option, $config[$iface])) {
		// Option isn't already an array
		if (!is_array($config[$iface][$option])) {
			$old_value = $config[$iface][$option];
			$config[$iface][$option] = Array();
			array_push($config[$iface][$option], $old_value);
		}
		array_push($config[$iface][$option], $value);
		return;
	}

	// Normal addition
	$config[$iface][$option] = $value;
	return;
}

function interfaces_read($filename) {
	$interfaces_f = file($filename);

	$iface = NULL;
	$family = NULL;
	$config = Array();

	foreach ($interfaces_f as $line) {
		// Clean the line for parsing
		$clean_line = trim($line);

		// Comments and blank lines
		if ($clean_line[0] == '#' || empty($clean_line)) {
			continue;
		}

		// Auto
		if (preg_match("/^auto[\s]+([^\s]+)$/", $clean_line, $matches)) {
			config_add($config, $matches[1], "auto", true);
			continue;
		}

		// Allow (-hotplug)
		if (preg_match("/^allow-([^\s]+)[\s]+([^\s]+)$/", $clean_line, $matches)) {
			// TODO
			config_add($config, $matches[2], "allow", $matches[1]);
			continue;
		}

		// Iface
		if (preg_match("/^iface[\s]+([^\s]+)[\s]+([^\s]+)[\s]([^\s]+)$/", $clean_line, $matches)) {
			$iface = $matches[1];
			$family = $matches[2];
			$method = $matches[3];
			config_add($config, $iface, "method", $method);
			continue;
		}

		// Options
		if (!$iface || !$family) {
			return false;
		}
		if (preg_match("/^([^\s]+)[\s]+(.*)$/", $clean_line, $matches)) {
			config_add($config, $iface, $matches[1], $matches[2]);
			continue;
		}

		// Oops!
		echo $clean_line . " OOPS!";
		return false;
	}

	return $config;
}

function interfaces_reserved() {
	$interfaces = Array();
	$interfaces = interfaces_scan($interfaces, "/etc/network/interfaces");
	$dir = opendir("/etc/network/interfaces.d/");
	while (false != ($file = readdir($dir))) {
		if ($file[0] == ".") {
			continue;
		}
		if ($file == "piNetConfig") {
			continue;
		}
		$interfaces = interfaces_scan($interfaces, "/etc/network/interfaces.d/" . $file);
	}
	closedir($dir);
	return $interfaces;
}

//echo json_encode(interfaces_read("/etc/network/interfaces"));

?>
