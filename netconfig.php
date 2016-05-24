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

function interfaces_reserved() {
	$interfaces = Array();
	$interfaces = interfaces_scan($interfaces, "/etc/network/interfaces");
	$dir = opendir("/etc/network/interfaces.d/");
	while (false != ($file = readdir($dir))) {
		if ($file[0] == ".") {
			continue;
		}
		if ($file == "autojson") {
			continue;
		}
		$interfaces = interfaces_scan($interfaces, "/etc/network/interfaces.d/" . $file);
	}
	closedir($dir);
	return $interfaces;
}

echo json_encode(interfaces_reserved());

?>
