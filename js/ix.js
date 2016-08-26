function ix_iface_block_add ($container, name, data, config) {
	var block = { name: name, data: data, config: config, blocks: [] };
	block['$container'] = $container;

	var $head = block['$head'] = $('<div class="header">');
	$head.text(name);

	var $content = block['$content'] = $('<div class="content">');

	var $table = $('<table border="0">');
	$content.append($table);
	for (var setting in data) {
		var value_current = data[setting];
		var value_config = config ? config[setting] : null;
		var value = value_config ? value_config : value_current;
		var $tr = $('<tr>');
		var $th = $('<th>');
		$th.text(setting);
		var $td = $('<td>');
		if (typeof value === 'object') {
			block.blocks.push(ix_iface_block_add($td, setting, data[setting], config ? config[setting] : null));
		} else {
			$td.text(value);
		}
		$tr.append($th, $td);
		$table.append($tr);
	}

	$container.append($head, $content);
	return block;
}

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
			ix_iface_block_add($block, setting, data[setting], config ? config[setting] : null);
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
	$aliases.accordion();
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

	var $block = protocol['$block'] = $('<div style="border: 1px solid #000;">');
	var $header = $('<div style="padding: 5px; background-color: #AAA;">' + protocol_name + '</div>');
	var $table = $('<table border="0">');
	for (var setting in data) {
		$table.append($('<tr><th>' + setting + '</th><td>' + data[setting] + '</td></tr>'));
	}

	// Done
	$block.append($header, $table);
	$container.append($block);
	return protocol;
}
