<?php

function config_add(&$config, $iface, $family, $option, $value) {
	// Iface doesn't exist
	if ($config[$iface] == NULL) {
		$config[$iface] = Array();
	}
	$block =& $config[$iface];
	if ($family) {
		if ($block['protocol'] == NULL) {
			$block['protocol'] = Array();
			$block['protocol'][$family] = Array();
		} else if ($block['protocol'][$family] == NULL) {
			$block['protocol'][$family] = Array();
		}
		$block =& $block['protocol'][$family];
	}

	// Option already exists
	if (array_key_exists($option, $block)) {
		// Option isn't already an array
		if (!is_array($block[$option])) {
			$old_value = $block[$option];
			$block[$option] = Array();
			array_push($block[$option], $old_value);
		}
		array_push($block[$option], $value);
		return;
	}

	// Normal addition
	$block[$option] = $value;
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
			config_add($config, $matches[1], NULL, "auto", true);
			continue;
		}

		// Allow (-hotplug)
		if (preg_match("/^allow-([^\s]+)[\s]+([^\s]+)$/", $clean_line, $matches)) {
			// TODO
			config_add($config, $matches[2], NULL, "allow", $matches[1]);
			continue;
		}

		// Iface
		if (preg_match("/^iface[\s]+([^\s]+)[\s]+([^\s]+)[\s]([^\s]+)$/", $clean_line, $matches)) {
			$iface = $matches[1];
			$family = $matches[2];
			$method = $matches[3];
			config_add($config, $iface, $family, "method", $method);
			continue;
		}

		// Options
		if (!$iface || !$family) {
			return false;
		}
		if (preg_match("/^([^\s]+)[\s]+(.*)$/", $clean_line, $matches)) {
			config_add($config, $iface, $family, $matches[1], $matches[2]);
			continue;
		}

		// Oops!
		return false;
	}

	return $config;
}

//echo json_encode(interfaces_read("/etc/network/interfaces"));

?>
