function ix_iface_add ($tab_block, iface_name, data, config) {
	// iface tracking structure
	var iface = { name: iface_name, data: data, aliases: [] };

	// HTML header for interface
	var $head = iface['$head'] = $('<li><a href="#interface-' + iface_name + '">' + iface_name + '</a></li>');

	// HTML block for interface
	var $block = iface['$block'] = $('<div class="interface">');
	$block.attr('id', 'interface-' + iface_name);

	// HTML block for aliases
	var $aliases = iface['$aliases'] = $('<div class="interface-aliases">');

	// HTML table for settings
	var $table = $('<table border="0">');
	for (var setting in data) {

		// Wireless Block
		if (setting == 'wireless') {
			$block.append('<h6>Wireless</h6>');
			continue;
		}

		// Logical Interface
		if (setting.startsWith(iface_name)) {
			iface.aliases.push(ix_iface_alias_add($aliases, iface, setting, data[setting], config[setting]));
			continue;
		}

		$table.append($('<tr><th>' + setting + '</th><td>' + data[setting] + '</td></tr>'));
	}

	// Done
	$block.append($table, $aliases);
	$tab_block.children('ul').first().append($head);
	$tab_block.append($block);
	return iface;
}

function ix_iface_alias_add ($accordian_block, iface, alias_name, data, config) {
	// alias tracking structure
	var alias = { iface: iface, "name": alias_name, data: data, protocols: [] };

	// HTML header for alias
	var $head = alias['$head'] = $('<h3>');
	$head.text(alias_name);

	// HTML block for alias
	var $block = alias['$block'] = $('<div class="interface-alias">');
	$block.attr('id', 'interface-' + iface.name + '-' + alias_name);

	// HTML block for protocols
	var $protocols = $('<div>');

	// HTML table for settings
	var $table = $('<table border="0">');
	for (var setting in data) {

		// Protocol Block
		if (typeof data[setting] === 'object' && !Array.isArray(data[setting])) {
			alias.protocols.push(ix_iface_alias_protocol_add($protocols, alias, setting, data[setting], config[setting]));
			continue;
		}

		$table.append($('<tr><th>' + setting + '</th><td>' + data[setting] + '</td></tr>'));
	}

	// Done
	$block.append($table, $protocols);
	$accordian_block.append($head, $block);
	return alias;
}

function ix_iface_alias_protocol_add($container, alias, protocol_name, data, config) {
	var protocol = { name: protocol_name, alias: alias, iface: alias.iface };

	var $block = protocol['$block'] = $('<div>');
	var $header = $('<h6>' + protocol_name + '</h6>');
	var $table = $('<table border="0">');
	for (var setting in data) {
		$table.append($('<tr><th>' + setting + '</th><td>' + data[setting] + '</td></tr>'));
	}

	// Done
	$block.append($header, $table);
	$container.append($block);
	return protocol;
}
