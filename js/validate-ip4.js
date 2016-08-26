function ip4_parse_address(s) {
	var octets_s = s.split(".");
	var octets = [];
	
	if (octets_s.length != 4) {
		return false;
	}
	
	var i;
	for (i = 0; i < 4; i++) {
		if (!octets_s[i].match("^[0-9]([0-9]([0-9])?)?$")) {
			return false;
		}

		var octet = parseInt(octets_s[i]);
		if (octet < 0 || octet > 255) {
			return false;
		}
		
		octets[i] = octet;
	}

	return octets;
}

function ip4_parse_netsize(octets) {
	var size = 0;

	var i;
	for (i = 0; i < 4; i++) {
		if (i * 8 != size && octets[i] != 0) {
			return false
		}

		switch (octets[i]) {
		case 255:
			size++;
		case 254:
			size++;
		case 252:
			size++;
		case 248:
			size++;
		case 240:
			size++;
		case 224:
			size++;
		case 192:
			size++;
		case 128:
			size++;
		case 0:
			break;
		default:
			return false;	// octet has a gap in it
		}
	}

	return size;
}

function ip4_parse_netsize2mask(size) {
	var octets = [];
	
	var full = size/8;
	var rem = size%8;

	var i;
	for (i = 0; i < full; i++) {
		octets[i] = 255;
	}
	
	switch (rem) {
		case 0:
			octets[i] = 0;
			break;
		case 1:
			octets[i] = 128;
			break;
		case 2:
			octets[i] = 192;
			break;
		case 3:
			octets[i] = 224;
			break;
		case 4:
			octets[i] = 240;
			break;
		case 5:
			octets[i] = 248;
			break;
		case 6:
			octets[i] = 252;
			break;
		case 7:
			octets[i] = 254;
			break;
		case 8:
			octets[i] = 255;
			break;
		default:
			return false;	// impossible
	}

	for (;i < 4; i++) {
		octets[i] = 0;
	}

	return octets;
}

function ip4_validate_address(octets) {
	if (!octets) {
		return false;
	}

	if (octets[0] == 0 || octets[0] == 127 || (octets[0] > 224 && octets[0] < 240)) {
		return false;
	}

	if (octets[0] == 255 && octets[1] == 255 && octets[2] == 255 && octets[3] == 255) {
		return false;
	}

	return true;
}

function ip4_mask_address(ip, mask) {
	var ret = [];

	var i;
	for (i = 0; i < 4; i++) {
		ret[i] = ip[i] & mask[i];
	}

	return ret;
}

function ip4_compare_address(ip1, ip2) {
	var i;
	for (i = 0; i < 4; i++) {
		if (ip1[i] != ip2[i]) {
			return false;
		}
	}
	return true;
}

function ip4_validate_network(ip_s, netmask_s, gateway_s) {
	var ip = ip4_parse_address(ip_s);
	var gateway = gateway_s ? ip4_parse_address(gateway_s) : null;
	var netmask = ip4_parse_address(netmask_s);

	if (!ip || !netmask || (gateway_s && !gateway)) {
		return false;
	}

	if (!ip4_validate_address(ip) || (gateway_s && !ip4_validate_address(gateway))) {
		return false;
	}

	var netsize = ip4_parse_netsize(netmask);
	if (!netsize || netsize < 8 || netsize > 30) {
		return false;
	}

	var ip_net = ip4_mask_address(ip, netmask);

	if (gateway) {
		var gateway_net = ip4_mask_address(gateway, netmask);
		if (!ip4_compare_address(gateway_net, ip_net)) {
			return false;
		}
		if (ip4_compare_address(gateway, ip)) {
			return false;
		}
	}

	var broadcast = [];
	var i;
	for (i = 0; i < 4; i++) {
		broadcast[i] = ip_net[i] | (~netmask[i] & 0xFF);
	}
	if (ip4_compare_address(broadcast, ip) || (gateway_s && ip4_compare_address(gateway, broadcast))) {
		return false;
	}

	if (netsize >= 24) {
		if (ip[3] == 0 || ip[3] == 255) {
			return false;
		}
		if (gateway && (gateway[3] == 0 || gateway[3] == 255)) {
			return false;
		}
	}

	return true;
}


