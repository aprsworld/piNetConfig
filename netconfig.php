<?php
require('netconfig-write.php');
require('netconfig-current.php');
require('netconfig-validate.php');
require('netconfig-read.php');

$config = json_decode(file_get_contents('php://input'), true);
if ($config) {
	return netconfig_write($config['config']);
} else {
	$iwconfig_output = shell_exec("/sbin/iwconfig 2> /dev/null");
	if ($iwconfig_output == NULL) {
		$iwconfig = Array();
	} else {
		$iwconfig = iwconfig_parse($iwconfig_output);
	}

	$ip_output = shell_exec("/sbin/ip -o -f inet link 2> /dev/null");
	if ($ip_output == NULL) {
		exit(-1);
	}
	$ip_link = ip_parse_link($ip_output);

	$ip_output = shell_exec("/sbin/ip -o -f inet route 2> /dev/null");
	if ($ip_output == NULL) {
		exit(-1);
	}
	$ip_route = ip_parse_route($ip_output);
	
	$ip_output = shell_exec("/sbin/ip -o -f inet addr 2> /dev/null");
	if ($ip_output == NULL) {
		exit(-1);
	}
	$ip = ip_parse_address($ip_output);

	$config = array_merge_recursive($ip_link, $ip_route, $ip, $iwconfig);
	$config['config'] = interfaces_read("/etc/network/interfaces");
	$config['config']['system'] = Array();
	if (array_key_exists('source', $config['config'])) {
		if (!is_array($config['config']['source'])) {
			$config['config']['source'] = Array($config['config']['source']);
		}
		foreach ($config['config']['source'] as $file) {
			echo $file;
			$sysconfig = interfaces_read($file);
			$config['config']['system'][$file] = $sysconfig;
		}
	}
	if (array_key_exists('source-directory', $config['config'])) {
		if (!is_array($config['config']['source-directory'])) {
			$config['config']['source-directory'] = Array($config['config']['source-directory']);
		}
		foreach ($config['config']['source-directory'] as $dir) {
			$dir = rtrim($dir, "/");
			$files = scandir($dir);
			foreach ($files as $file) {
				if (substr($file, 1) == ".") {
					continue;
				}
				if (is_dir($dir . '/' . $file)) {
					continue;
				}
				$sysconfig = interfaces_read($dir . '/' . $file);
				$config['config']['system'][$dir . '/' . $file] = $sysconfig;
			}
		}
	}
	echo json_encode($config);
	return 0;
}
?>
