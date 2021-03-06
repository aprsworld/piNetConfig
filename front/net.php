<html>
<head>
<title>Network Configuration</title>
<link rel="stylesheet" href="res/jquery-ui-1.11.4.custom/jquery-ui.min.css" />
<script src="res/jquery-2.2.4.min.js"></script>
<script src="res/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
<script src="js/validate-ip4.js"></script>
<style>
label {
	width: 100%;
}
body {
	background-color: white;
}
#currentsettingstimer{
	text-align: right;
}
/* Switch code found at http://www.w3schools.com/howto/tryit.asp?filename=tryhow_css_switch*/
.switch {
  position: relative;
  display: inline-block;
  width: 75px;
  height: 35px;
}
.onoffswitch {
	width: 120px;
	text-align: center;
}
.switch input {display:none;}

.slider {
	border: 1px solid #ccc;

	text-align: left !important;
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #aaa;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
	border: 1px solid #aaa;

	font-size: 12px;
  position: absolute;
	text-align: center;
	line-height: 3.0em;
	color: #333;
  content: "OFF";
  height: 34px;
  width: 34px;
  left: 5px;
  bottom: -1px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}


input:checked + .slider {
  background-color: #2196F3;
}

input:checked + .slider:before {
	content: "ON"
}


input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 24px;
}

.slider.round:before {
  border-radius: 50%;
}
.main-h1{
	font-family: sans-serif;
	color: #333;
}
.ui-tabs-nav{
	background-color: white !important;
	border-top: none !important;
	border-left: none !important;
	border-right: none !important;
}
.ui-textfield {
	font:		inherit;
	color:		inherit;
	background:	none;
	background-color:	#FFF !important;
	text-align:	inherit;
	outline:	none;
	cursor:		text;
}
.ui-state-focus, .ui-state-focus > a {
	outline:		none !important;
}
.ui-selectmenu-button {
	background-color:	#007FFF !important;
	border-color:		#003ECF !important;
	color:			#FFF !important;
}
.ui-button-text{
	-webkit-touch-callout: none; /* iOS Safari */
  -webkit-user-select: none;   /* Chrome/Safari/Opera */
  -khtml-user-select: none;    /* Konqueror */
  -moz-user-select: none;      /* Firefox */
  -ms-user-select: none;       /* Internet Explorer/Edge */
  user-select: none;           /* Non-prefixed version, currently
                                  not supported by any browser */
}
.ui-button:not(.ui-state-disabled, .ui-textfield):hover{
    border: 1px solid #333 !important;
}
.wifi-scan-button, .wifi-scan-list, .drop-down-label, .drop-down{
	vertical-align: middle;
	margin: 5px 5px 5px 0px;
}
.drop-down-label{
	display: inline-block;
	text-align: center;
	width: 124px;
}
.system-settings-label, .current-settings-label{
	font-weight: 300;
	display: inline-block;
	text-align: left !important;

}
.current-settings-tr:nth-child(even){

}
.current-settings-tr > td{
	color: #454545;

}
.current-settings-tr + .current-settings-tr > th{
	border-top: 1px solid #ccc;
}
.current-settings-tr + .current-settings-tr > td{
	border-top: 1px solid #ccc;
}
.current-settings-th{
	width: 300px;
	color: #454545;
	font-weight: 300
}
.current-settings-th:odd{
	background-color: #ccc;

}
.ui-icon-triangle-1-s{
	color: #fff !important;
	border-color: #fff !important;
	background-image: url(http://localhost/piNetConfig/res/jquery-ui-1.11.4.custom/images/ui-icons_ffffff_256x240.png) !important;
}
.ui_changed {
	background-color:	#7FCF7F !important;
	border-color:		#7F7F7F !important;
	color:			#000 !important;
}
.ui_changed.ui-state-active, .ui_changed.ui-selectmenu-button {
	background-color:	#007F00 !important;
	border-color:		#003E00 !important;
	color:			#FFF !important;
}
.ui_error {
	background-color:	#FFCFCF !important;
	border-color:		#7F7F7F !important;
	color:			#000 !important;
}
.ui_error.ui-state-active, .ui_error.ui-selectmenu-button {
	background-color:	#7F0000 !important;
	border-color:		#3E0000 !important;
	color:			#FFF !important;
}
</style>
</head>

<body>
<h1 class="main-h1" style="float: left;">Network Configuration <?php echo "- " . gethostname(); ?> </h1>

<div style="float: right;" id="buttons">
<input id="reset" type="button" name="reset" value="Reset"></input>
<input id="update" type="button" name="update" value="Update"></input>
</div>

<div style="clear: both;" id="interfaces">
<ul></ul>
</div>

<script>
if (!String.prototype.startsWith) {
	String.prototype.startsWith = function(searchString, position) {
		position = position || 0;
		return this.indexOf(searchString, position) === position;
	};
}
if (!String.prototype.endsWith) {
	String.prototype.endsWith = function(suffix) {
    return this.indexOf(suffix, this.length - suffix.length) !== -1;
	};
}
var _current_settings_items = {};
$("#update").prop('disabled', true);
$("input:button").button();
$("#reset").click(function (event) {
	location.reload();
});
$("#update").click(function (event) {
	var config = {};
	$(".interface").not('#interface-current-settings, #interface-system-settings').each(function (index, el) {
		var iface = $(this).attr('id').substring(10);

		config[iface] = {
			mtu: $(this).find('input[name="mtu"]').first().val()
		};
		$(this).find('.alias').each(function (index, el) {
			var alias = $(this).attr('id').substring(iface.length + 11);
			if (alias != iface) {
				config[alias] = {};
			}
			if(iface.startsWith("wlan")){
				config[alias].allow = ["hotplug", "auto"];
			}
			else{
				config[alias].allow = "auto";
			}
			config[alias].protocol = {};
			config[alias].protocol.inet = {};

			var method = $(this).find('.ui-selectmenu-button:first').children('.ui-selectmenu-text').text().toLowerCase();
			if(method != undefined){
				config[alias].protocol.inet["method"] = method;
			}
			$(this).find('input[type=text]').each(function (index, el) {
				if ($(this).prop('disabled')) {
					return;
				}
				var option = $(this).prop('name');
				var value = $(this).val();
				if (value.trim() == "" || value == "undefined") {
					return;
				}
				// add quotes around wpa-psk value because config requries this
				if ($(this).attr('name') == 'wpa-psk'){
					value = "\""+value+"\"";
					config[alias].protocol.inet["wpa-ap-scan"] = "1";
					config[alias].protocol.inet["wpa-scan-ssid"] = "1";
				}
				if (config[alias].protocol.inet[option] != undefined) {
					if ($.isArray(config[alias].inet[option])) {
						config[alias].protocol.inet[option].push(value);
					} else {
						config[alias].protocol.inet[option] = [ config[alias].protocol.inet[option], value ];
					}
				} else {
					config[alias].protocol.inet[option] = value;
				}
			});
		});
	});
	console.log(config);
	var finalConfig = JSON.stringify({"config":config});
	console.log(finalConfig);
	alert(finalConfig);
	$.ajax({
			url: "netconfig.php",
			type: 'post',
			dataType: 'JSON',
			contentType: 'application/json',
			data: finalConfig ,
			cache: false,
			success: function(data) {
				console.log(data);
			},
			error: function (error) {
				console.log(error);
			}
	});
});

function get_ssids($wifi_list, iface){
	$.ajax({
				url: "netconfig-scan.php",
				type: 'post',
				dataType: 'JSON',
				contentType: 'application/json',
				cache: false,
				success: function(returned_data) {
					console.log(returned_data);
					$wifi_list.children('option[value="placeholder"]').remove();
					$wifi_list.append('<option value="" disabled selected>Choose an SSID</option>');
					for( key in returned_data[iface]){
							$wifi_list.append('<option value="'+key+'">'+key+'</option>');
					}
					$($wifi_list).selectmenu("refresh");
				},
				error: function (error) {
					console.log(error);
					$wifi_list.children('option[value="placeholder"]').remove();
					$wifi_list.append('<option value="placeholder">No SSIDs Found</option>');
				}

			});
}

function ui_changed($input) {
	var $label = $input.data('$label');
	if (!$label) {
		$label = $input.next();
	}
	var $checkbox = $input.data('$checkbox');
	if (!$checkbox) {
		$checkbox = $('<input type="checkbox" checked></input>');
	}
	var $iface = $input.data('$iface');
	var $iface_widget = $input.data('$iface_widget');
	var $alias = $input.data('$alias');
	var $alias_widget = $input.data('$alias_widget');
	var disabled = $checkbox.prop('disabled');
	var set = $checkbox.prop('checked');
	var value = $input.val();
	var value_orig = $input.data('value_orig');

	if ((!disabled && set) && $input.prop('disabled')) {
		$input.button('enable');
	} else if ((disabled || !set) && !$input.prop('disabled')) {
		$input.button('disable');
	}
	console.log(value_orig, value);
	if (disabled || (!set && value_orig == undefined || set && value_orig.replace(/\"/g, "") == value)) {
		if (!$label.hasClass("ui_changed")) {
			return;
		}
		$label.removeClass("ui_changed");
		$label.removeClass("ui_error");
		if ($alias) {
			if ($alias.find(".ui_error").size() == 0) {
				$alias_widget.removeClass("ui_error");
			}
			if ($alias.find(".ui_changed").size() == 0) {
				$alias_widget.removeClass("ui_changed");
			}
		}
		if ($iface.find(".ui_error").size() == 0) {
			$iface_widget.removeClass("ui_error");
		}
		if ($iface.find(".ui_changed").size() == 0) {
			$iface_widget.removeClass("ui_changed");
		}

		return;
	}

	var type = $input.attr('name');
	if ($alias && (type == 'address' || type == 'netmask' || type == 'gateway')) {
		var error = false;

		if ((!set && type != 'gateway') || !ip4_validate_address(ip4_parse_address(value))) {
			error = true;
		} else {
			var $address = $alias.find('input[type="text"][name="address"]');
			var $netmask = $alias.find('input[type="text"][name="netmask"]');
			var $gateway = $alias.find('input[type="text"][name="gateway"]');
			var address = null;
			if ($address.data('$checkbox').prop('checked') && ($address != $input && !$address.data('$label').hasClass("ui_error"))) {
				address = $address.val();
			}
			var netmask = null;
			if ($netmask.data('$checkbox').prop('checked') && ($netmask != $input && !$netmask.data('$label').hasClass("ui_error"))) {
				netmask = $netmask.val();
			}
			var gateway = null;
			if ($gateway.data('$checkbox').prop('checked') && ($gateway != $input && !$gateway.data('$label').hasClass("ui_error"))) {
				gateway = $gateway.val();
			}
			if (address && netmask && !ip4_validate_network(address, netmask, gateway)) {
				error = true;
			}
		}

		if (error) {
			if (!$label.hasClass("ui_error")) {
				$label.addClass("ui_error");
				if ($alias) {
					$alias_widget.addClass("ui_error");
				}
				$iface_widget.addClass("ui_error");
			}
		} else {
			if ($label.hasClass("ui_error")) {
				$label.removeClass("ui_error");
				if ($alias && $alias.find(".ui_error").size() == 0) {
					$alias_widget.removeClass("ui_error").removeClass("ui_error");
				}
				if ($iface.find(".ui_error").size() == 0) {
					$iface_widget.removeClass("ui_error");
				}
			}
		}
	}

	if ($label.hasClass("ui_changed")) {
		return;
	}
	$label.addClass("ui_changed");
	if ($alias) {
		$alias_widget.addClass("ui_changed");
		console.log($alias);

	}

	$iface_widget.addClass("ui_changed");
}

function determine_wifi_options(config, data, $table, iface, alias, $alconfig) {
	var available = false;
	if (alias && config[alias] && config[alias].protocol && config[alias].protocol.inet){
		available = true;
	}
	var securityType = "none";
	if(available && config[alias].protocol.inet.hasOwnProperty('wpa-ssid')){
		securityType = "wpa2";
		console.log(securityType);
		add_ssid_option(config, data, $table, 'wpa-ssid', 'SSID', iface, alias, false);
		add_ssid_option(config, data, $table, 'wpa-psk', 'Pass Key', iface, alias, false);

	}
	else if(available && config[alias].protocol.inet.hasOwnProperty('wireless-essid')){
		securityType = "wep";
		console.log(securityType);
		add_ssid_option(config, data, $table, 'wireless-essid', 'SSID', iface, alias, false);
		add_ssid_option(config, data, $table, 'wireless-key', 'Pass Key', iface, alias, false);

	}
	else{
		console.log(securityType);
		add_ssid_option(config, data, $table, 'wireless-essid', 'SSID', iface, alias, false);
		add_ssid_option(config, data, $table, 'wireless-key', 'Pass Key', iface, alias, true);
	}
	var $config_select = $('<select name="security"></select>');
	$config_select.append('<option value="none">None</option>');
	$config_select.append('<option value="wep">WEP</option>');
	$config_select.append('<option value="wpa">WPA</option>');
	$config_select.append('<option value="wpa2">WPA2</option>');

	// set the value of our select menu and keep track of what it was originally
	$config_select.val(securityType);
	$config_select.data('value_orig', securityType);

	var $wifi_list = $('<select id="'+iface+'-'+alias+'-wifi_list" name="wifi_list"></select>');
	var $scan_button = $('<input id="'+iface+'-'+alias+'-wifi_scan" type="button" name="'+iface+'-'+alias+'-wifi_scan" value="Scan for Wifi"></input>');
	$wifi_list.append('<option value="placeholder">Scanning...</option>');
	$scan_button.addClass('wifi-scan-button')
	get_ssids($wifi_list, iface);

	$alconfig.append('<h3> Security Options </h3>');
	$alconfig.append('<label class="drop-down-label" for="">Security Type</label>');
	$alconfig.append($config_select);
	$alconfig.append('<br>');
	$alconfig.append($scan_button);
	$scan_button.button({
		width: 192
	});
	$($scan_button).click(function(ev){

		$wifi_list.find('option')
    .remove()
    .end()
    .append('<option value="placeholder">Scanning...</option>')
    .val('placeholder');
		$wifi_list.selectmenu( "refresh" );
		get_ssids($wifi_list, iface);
	});
	$alconfig.append($wifi_list);
	// create jquery combobox
	$wifi_list.selectmenu({
		width: 192,
		change: function (ev) {
			console.log(ev);
			$('.ssid-option').filter(function(index) {
				if($( this ).attr("id").indexOf(iface+"-"+alias)){
					return $(this);
				}
			}).val($(this).val());

		}
	});
	$wifi_list.selectmenu( "widget" ).addClass("wifi-scan-list");
	$config_select.selectmenu({
		width: 192,
		change: function (ev) { //event handler for changing values on select
			console.log($(this).data('$inputs'));
			var that = $(this);
			if ($(this).val() == 'none') {
				console.log($(this).data('$inputs'));
				//$(this).data('$inputs').filter(':input[type=checkbox]').button('enable');
				$(this).data('$inputs').each(function(index){
					var newid = $(this).attr('id');
					newid = newid.replace("wpa-psk", "wireless-key").replace("wpa-ssid", "wireless-essid");
					var newname = $(this).attr('name');
					newname = newname.replace("wpa-psk", "wireless-key").replace("wpa-ssid", "wireless-essid");
					$(this).attr('id', newid);
					$(this).attr('name', newname);
					console.log(newid);
					console.log(newname);
					var label = $(this).data('$label');
					// for some reason a function is required to replace the for attribute value
					$(label).attr('for', function(i, val) {
						return val.replace("wpa-psk", "wireless-key").replace("wpa-ssid", "wireless-essid");
					});
				});
				$(this).data('$inputs').filter('input[type=checkbox]').button('enable');
				var pass = $(this).data('$inputs').filter('input[type=checkbox]').filter('input[name*="wireless-key"]');
				pass.button('disable');
			} else if($(this).val() == 'wep') {
				//$(this).data('$inputs').filter('input[type=checkbox]').button('disable');

				$(this).data('$inputs').each(function(index){
					var newid = $(this).attr('id');
					console.log($(this).data());
					newid = newid.replace("wpa-psk", "wireless-key").replace("wpa-ssid", "wireless-essid");
					var newname = $(this).attr('name');
					newname = newname.replace("wpa-psk", "wireless-key").replace("wpa-ssid", "wireless-essid");
					$(this).attr('id', newid);
					$(this).attr('name', newname);
					console.log(newid);
					console.log(newname);
					var label = $(this).data('$label');
					// for some reason a function is required to replace the for attribute value
					$(label).attr('for', function(i, val) {
						return val.replace("wpa-psk", "wireless-key").replace("wpa-ssid", "wireless-essid");
					});
				});
				$(this).data('$inputs').filter('input[type=checkbox]').button('enable');

			} else if($(this).val() == 'wpa' || ($(this).val() == 'wpa2')) {
				console.log(	$(this).data());
				$(this).data('$inputs').each(function(index){
					var newid = $(this).attr('id');
					newid = newid.replace("wireless-key", "wpa-psk").replace("wireless-essid", "wpa-ssid");
					var newname = $(this).attr('name');
					newname = newname.replace("wireless-key", "wpa-psk").replace("wireless-essid", "wpa-ssid");
					$(this).attr('id', newid);
					$(this).attr('name', newname);
					console.log(newid);
					console.log(newname);
					var label = $(this).data('$label');

					$(label).attr('for', function(i, val) {
						return val.replace("wireless-key", "wpa-psk").replace("wireless-essid", "wpa-ssid");
					});
				});
				$(this).data('$inputs').filter('input[type=checkbox]').button('enable');

			}
			console.log($(this).data('$inputs'));
			$(this).data('$inputs').filter('input[type=checkbox]').each(function (ev) {
				ui_changed($(this).data('$input'));
			});
			ui_changed($(this));
		}
	});
	$config_select.selectmenu( "widget" ).addClass("drop-down");
	$config_select.data('$inputs', $table.find('input'));


}

/* checkNested(obj, pathArr)
** Checks for a nested property within an object
** obj is the object we are searching through,
** pathArr is the array of nested keys.
** returns an object that states whether nested key was found add_nested_setting
** the value of said key.
*/
function checkNested(obj, pathArr) {
  for (var i = 0; i < pathArr.length; i++) {
    if (!obj || !obj.hasOwnProperty(pathArr[i])) {
      return {found:false, result:null};
    }
    obj = obj[pathArr[i]];
  }
  return {found:true, result:obj};
}
function changeSystemOption(feature){
	$.ajax("shExecute.php", {
		type: 'POST',
		context:	this,
		cache:		false,
		data: {
			'feature' : feature
		},
		headers:	{},
		timeout: 5000,
		success: function(data, status, XHR) {
			console.log(data);
			//alert('success');
		},
		error: function() {
			//alert('failure');
		},
		complete: function() {
		}
	});
}
function add_system_setting(data, $table, setting, title, script){

	//create the label
	var $tr = $('<tr class="system-settings-tr">');
	var $label = $('<label class="system-settings-label">'+title+'</label>');
	var option_id = 'systemsettings_'+setting;
	var $th = $('<th class="system-settings-th"></th>').append($label);
	$tr.append($th);

	//create the checkbox switch
	var $td = $('<td  class="onoffswitch"></td>');
	var $switchLabel = $('<label class="switch"></label>');
	var $cbox = $('<input type="checkbox" checked>');
	var $slider = $('<div class="slider round">  </div>');
	$switchLabel.append($cbox);
	$switchLabel.append($slider);
	$td.append($switchLabel);
	$tr.append($td);
	$table.append($tr);
	//setup click event for checkbox
	$($slider).click(function(){
		changeSystemOption(script);
	});
}
function build_system_settings(config, data, $ifconfig){
	var $sys_settings_table = $('<table border="0"></table>');
	add_system_setting('', $sys_settings_table, 'webconfig', 'Allow Web Config', 'test1');
	add_system_setting('', $sys_settings_table, 'ssh', 'Allow SSH', 'test2');
	$ifconfig.append($sys_settings_table);
}
function build_system_settings1(config, data, $ifconfig){
	var $globals_table = $('<table border="0"></table>');
	var $webConfig = $('<label> Allow Web Config </label>')
	var $button1 = $('<input id="script1" type="button" value="script1"></input>');
	$button1.button();
	var $sshConfig = $('<label> Allow SSH </label>')
	var $button2 = $('<input id="script2" type="button" value="script2"></input>');
	$button2.button();
	$ifconfig.append($sshConfig);
	$ifconfig.append($button1);
	$ifconfig.append('<br>');
	$ifconfig.append($webConfig);
	$ifconfig.append($button2);
	$($button1).click(function(){
		changeSystemOption('test1');

	});
	$($button2).click(function(){
		changeSystemOption('test2');

	});

}
function build_current_settings(config, data, $ifconfig){
	for( var iface in data){
		console.log("---------------------------"+iface+"---------------------------");
		$ifaces = $('<div class="config-interface"></div>');
		var $iface_widget = null;
		if(iface.startsWith("wlan")){
			$iface_widget = $('<h3> Wireless (' + iface + ') </h3>');
		}
		else if(iface.startsWith("eth")){
			$iface_widget = $('<h3> Ethernet (' + iface + ') </h3>');
		}
		else if(iface.startsWith("lo")){
			$iface_widget = $('<h3> Loopback (' + iface + ') </h3>');
		}
		$ifaces.append($iface_widget);
		var $alconfig = $('<div class="alias"></div>');
		$ifconfig.append($ifaces);
		$alconfig.append('<h3>Interface Settings</h3>')
		//$alconfig.attr('id', 'interface-' + iface + '-' + alias);
		var $globals_table = $('<table border="0"></table>');
		add_nested_setting(data, [iface, 'state'], $globals_table, "Link State");
		add_nested_setting(data, [iface, 'mtu'], $globals_table, 'Max Transmission Unit');
		add_nested_setting(data, [iface, 'mode'], $globals_table, 'Mode');
		$alconfig.append($globals_table);
		if(iface.startsWith("wlan")){
			$alconfig.append('<h3>Wifi Settings</h3>')
			var $wifi_table = $('<table border="0"></table>');
			add_nested_setting(data, [iface,'wireless','settings', 'ESSID'], $wifi_table, 'Extended SSID');
			add_nested_setting(data, [iface,'wireless','settings', 'Signal level'], $wifi_table, 'Signal Level');
			$alconfig.append($wifi_table);
		}
		for( var alias in data[iface]){
			if(alias.startsWith("wlan") || alias.startsWith("eth")){
				$alconfig.append('<h3> '+alias+' Addressing</h3>')
				var $addressing_table = $('<table border="0"></table>');
				add_nested_setting(data, [iface, alias,'inet', 'address'], $addressing_table, 'IP Address');
				add_nested_setting(data, [iface, alias,'inet', 'netmask'], $addressing_table, 'Network Mask');
				add_nested_setting(data, [iface, alias,'inet', 'gateway'], $addressing_table, 'Gateway Address');
				add_nested_setting(data, [iface, alias,'inet', 'brd'], $addressing_table, 'Broadcast Address');
				$alconfig.append($addressing_table);
			}
		}
		$ifconfig.append($alconfig);

	}
	$ifconfig.accordion();
}

function add_nested_setting(data, pathArr, $table, title){
	var checked = checkNested(data, pathArr);
	if(checked.found){
		var $tr = $('<tr class="current-settings-tr">');
		var $label = $('<label class="current-settings-label">'+title+'</label>');
		var option_id = 'currentsettings_'+pathArr.join('-').replace(' ', '');
		var available = false;
		var option = pathArr[pathArr.length];
		var value = checked.result;
		var $th = $('<th class="current-settings-th"></th>').append($label);
		$tr.append($th);
		var $input = $('<span id="'+option_id+'" name="'+option+'">'+value+' </span>');
		var $td = $('<td></td>').append($input);
		_current_settings_items[option_id] = pathArr;
		console.log(_current_settings_items);
		$tr.append($td);
		$table.append($tr);
	}
}

function update_current_settings(){
	$.ajax("netconfig.php", {
		context:	this,
		cache:		false,
		dataType:	'json',
		ifModified:	true,
		headers:	{},
		timeout: 5000,
		retryAfter: 10000,
		success: function(data, status, XHR) {
			console.log(data);
			for( var key in _current_settings_items){
				var res = checkNested(data, _current_settings_items[key]);
				var id = '#currentsettings_'+_current_settings_items[key].join('-').replace(' ', '');
				console.log($(id).text());
				$(id).text(res.result);
			}
			clearInterval(csTimer.timerID);
			csTimer.timerID = setInterval(csTimerUpdate, 1000);
			csTimer.value = 0;
			$('#currentSettingsTimer').text(''+csTimer.value+' seconds since last update.');
			delete data;
		},
		error: function(error) {
			console.log(error);
		},
		complete: function() {
			setTimeout(update_current_settings, 5000);
		}
	});


	/*.done(function (data, status, XHR) {
		for( var key in _current_settings_items){
			var res = checkNested(data, _current_settings_items[key]);
			var id = '#currentsettings_'+_current_settings_items[key].join('-').replace(' ', '');
			console.log($(id).text());
			$(id).text(res.result);
		}
		setTimeout(update_current_settings, 5000);
		delete data;
	});*/
}

/* config = config object from json, data = current settings from json, $table is the html element that holds everything,
   option = address, netmask, etc. , title = human readable title for option, iface = interface we are workign with
   alias = ex: eth0:0 */
function add_option(config, data, $table, option, title, iface, alias) {
	var option_id = 'config-' + iface + '-' + (alias ? alias + '-' + option : option);
	var available = true;
	if (alias && config[alias] && config[alias].protocol && config[alias].protocol.inet && config[alias].protocol.inet.method != 'static')  {
		available = false;
	}
	var config_value = null;
	if (alias && config[alias] && config[alias].protocol && config[alias].protocol.inet) {
		config_value = config[alias].protocol.inet[option];
	} else if (config[iface]) {
		config_value = config[iface][option];
	}
	var checked = (config_value) ? " checked" : "";
	var disabled = (config_value) ? "" : " disabled";
	var value = (config_value) ? config_value : (alias) ? data[iface][alias].inet[option] : data[iface][option];
	if (value === null || value == undefined) {
		value = "";
	}

	var $tr = $('<tr>');
	var $label = $('<label for="'+option_id+'-set">'+title+'</label>');
	// hidden checkbox that determines whether or not the input is shown
	var $checkbox = $('<input id="'+option_id+'-set" type="checkbox" name="'+option+'"'+checked+'></input>');
	$checkbox.prop('disabled', !available);
	var $th = $('<th></th>').append($label).append($checkbox);
	$tr.append($th);
	var $input = $('<input type="text" id="'+option_id+'" name="'+option+'" value="'+value+'"'+disabled+'></input>');
	var $td = $('<td></td>').append($input);
	$tr.append($td);

	$checkbox.data('$input', $input);
	console.log(config_value);
	$input.data('value_orig', value);
	$input.data('$label', $label);
	$input.data('$checkbox', $checkbox);
	$input.button().addClass('ui-textfield').off('mouseenter').off('mousedown').off('keydown').bind('propertychange change keyup click keyup input paste', function(ev) {
		ui_changed($(this));
	});
	$checkbox.button().bind('blur click change keyup propertychange input', function(ev) {
		ui_changed($(this).data('$input'));
	});
	$table.append($tr);
}

function add_ssid_option(config, data, $table, option, title, iface, alias, disabled){
	var option_id = 'config-' + iface + '-' + (alias ? alias + '-' + option : option);
	var config_value = null;
	if (alias && config[alias] && config[alias].protocol && config[alias].protocol.inet) {
		console.log(1)
		config_value = config[alias].protocol.inet[option];
		console.log(config_value)
	} else if (config[iface]) {
		config_value = config[iface][option];
		console.log(2)

	}
	var checked = (config_value) ? " checked" : "";
	//var disabled = (config_value) ? "" : " disabled";

	var value = (config_value) ? config_value : (alias) ? data[iface][alias].inet[option] : data[iface][option];
	if (value === null || value == undefined) {
		value = "";
	}
	var $tr = $('<tr>');
	var $label = $('<label for="'+option_id+'-set">'+title+'</label>');
	// hidden checkbox that determines whether or not the input is shown
	var $checkbox = $('<input id="'+option_id+'-set" type="checkbox" name="'+option+'"'+checked+'></input>');
	$checkbox.prop('disabled', disabled);
	var $th = $('<th></th>').append($label).append($checkbox);
	$tr.append($th);
	var $input = $('<input type="text" id="'+option_id+'" name="'+option+'" value="'+value.replace(/"/g, "")+'"'+disabled+'></input>');
	if(option == 'wpa-ssid' || option == "wireless-essid"){
		$input.addClass('ssid-option');
	}
	var $td = $('<td></td>').append($input);
	$tr.append($td);

	$checkbox.data('$input', $input);
	$input.data('value_orig', config_value);
	$input.data('$label', $label);
	$input.data('$checkbox', $checkbox);
	$input.button().addClass('ui-textfield').off('mouseenter').off('mousedown').off('keydown').bind('propertychange change keyup click keyup input paste', function(ev) {
		ui_changed($(this));
	});
	$checkbox.button().bind('click change keyup propertychange input', function(ev) {
		ui_changed($(this).data('$input'));
	});
	$table.append($tr);
}

var csTimer = {
	value: 0,
	timerID: null
}

function csTimerUpdate(){
	csTimer.value++;
	$('#currentSettingsTimer').text(''+csTimer.value+' seconds since last update.');
}

$.ajax("netconfig.php", {
	context:	this,
	cache:		false,
	dataType:	'json',
	ifModified:	true,
	headers:	{}
}).done(function (data, status, XHR) {
	var config = data.config;
	delete data.config;
	//checknested test
	var $li = $('<li><a href="#interface-current-settings">Current Settings</a></li>');
	$('#interfaces ul').append($li);
	var $currentsettings = $('<div class="interface"></div>');
	$('#interfaces').append($currentsettings);
	$currentsettings.attr('id', 'interface-current-settings');
	$currentsettings.append('<div id="currentSettingsTimer"> '+csTimer.value+' seconds since last update. </div>');
	csTimer.timerID = setInterval(csTimerUpdate, 1000);
	$currentSettingsAccordion = $('<div id="currentSettingsAccordion"></div>');
	$currentsettings.append($currentSettingsAccordion);
	build_current_settings(config, data, $currentSettingsAccordion);
	//add_nested_setting(data, ['wlan0', 'wireless', 'type']);
	//console.log(checkNested(data, ['wlan0', 'wireless', 'type']));

	for (var iface in data) {
		// Create what will become the tabs for each interface at the top of the screen
		var iface_type = null;
		if(iface.startsWith('wlan')){
			iface_type = "Wireless";
		}
		else if(iface.startsWith('eth')){
			iface_type = "Ethernet"
		}
		else if(iface.startsWith('lo')){
			iface_type = "Loopback"
		}
		else{
			iface_type = ""
		}
		var $li = $('<li><a href="#interface-' + iface + '">' + iface_type+' (' + iface + ')</a></li>');
		$('#interfaces ul').append($li);
		var $ifconfig = $('<div class="interface"></div>');
		$('#interfaces').append($ifconfig);
		$ifconfig.attr('id', 'interface-' + iface);
		$ifconfig.append('<h2>'+ iface_type+' (' + iface + ')</h2>')
		$ifconfig_table = $('<table border=0>');
		// Add global options above the individual ifaces
		//add_option(config, data, $ifconfig_table, 'hwaddress', 'MAC Address', iface);
		//add_option(config, data, $ifconfig_table, 'mtu', 'MTU', iface);

		// set each button to "disabled"
		$ifconfig_table.find('input').button('disable');
		$ifconfig.append($ifconfig_table);
		$ifconfig.append('<br />');

		$ifconfig_aliases = $('<div class="config-interface"></div>');
		$ifconfig.append($ifconfig_aliases);
		var counter = 0;
		console.log(data[iface]);
		if(!iface in data[iface] && iface !== 'lo'){
			data[iface][iface] = {};
			data[iface][iface].inet = {}
			console.log(data[iface]);
		}
		// Create an empty alias if the interface has none
		if (iface in data[iface]){}
		else{
			data[iface][iface] = {};
			data[iface][iface].inet = {};
			console.log(config);
			console.log(data[iface]);
		}
		for (var alias in data[iface]) {
			//Exclude weird secondary ip addresses
			if(alias.endsWith("-secondary")){
				continue;
			}
			console.log(alias);
			var inet = data[iface][alias].inet;
			if (!inet) {
				continue;
			}
			counter++;
			var inet_config;
			// Default to DHCP if we do not see our iface within the config
			if (config[alias] == undefined){
				inet_config = {"method": "dhcp"};

			}
			else{
		 		inet_config = config[alias].protocol.inet;
			}
			console.log(alias)
			var $alconfig = $('<div class="alias"></div>');
			$alconfig.attr('id', 'interface-' + iface + '-' + alias);
			// create the selection menu that will become our jqueryui combobox
			var $config_select = $('<select name="method"></select>');
			if (inet_config.method == "dhcp" || inet_config.method == "static") {
				$config_select.append('<option value="dhcp">DHCP</option>');
				$config_select.append('<option value="static">Static</option>');
			} else {
				$config_select.append('<option>'+inet_config.method+'</option>');
			}
			// set the value of our select menu and keep track of what it was originally
			$config_select.val(inet_config.method);
			$config_select.data('value_orig', inet_config.method);
			$alconfig.append('<h3> Addressing Options </h3>');
			$alconfig.append($config_select);
			// create jquery combobox
			$config_select.selectmenu({
				width: 192,
				change: function (ev) { //event handler for changing values on select
					if ($(this).val() == 'static') {
						$(this).data('$inputs').filter('input[type=checkbox]').button('enable');
					} else {
						$(this).data('$inputs').filter('input[type=checkbox]').button('disable');
					}
					$(this).data('$inputs').filter('input[type=checkbox]').each(function (ev) {
						ui_changed($(this).data('$input'));
					});
					ui_changed($(this));
				}
			});
			var $alias_table = $('<table border="0"></table>');
			var $ssid_table = $('<table border="0"></table>');
			//add_option(config, data, $alias_table, 'hostname', 'Hostname', iface, alias);
			add_option(config, data, $alias_table, 'address', 'Address', iface, alias);
			add_option(config, data, $alias_table, 'netmask', 'Netmask', iface, alias);
			//add_option(config, data, $alias_table, 'pointopoint', 'Point to Point', iface, alias);
			add_option(config, data, $alias_table, 'gateway', 'Gateway', iface, alias);

			//add_option(config, data, $alias_table, 'metric', 'Metric', iface, alias);
			//add_option(config, data, $alias_table, 'nameserver', 'Nameserver', iface, alias);
			$alconfig.append($alias_table);
			if(iface.startsWith("wlan")){
				determine_wifi_options(config, data, $ssid_table, iface, alias, $alconfig);
			}
			$alconfig.append($ssid_table);
			$config_select.data('$inputs', $alias_table.find('input'));
			var $alias_widget = $('<h3>' + alias + '</h3>');
			$ifconfig_aliases.append($alias_widget);
			$ifconfig_aliases.append($alconfig);
			$alconfig.find('input, select').data('$alias', $alconfig)
					.data('$alias_widget', $alias_widget);
		}

 		$ifconfig_aliases.accordion();
		$ifconfig.find('input, select').data('$iface', $ifconfig)
				.data('$iface_widget', $li);
/*
			$alconfig.html('\
	<div class="config-alias">\
		<span class="title">Configuration Method:</span>\
		<span class="form">\
			<select class="configure-method" name="method">\
				<option value="dhcp">DHCP</option>\
				<option value="static">Static</option>\
			</select>\
		</span>\
		<table border=0>\
		<!-- <tr><th>Scope:</th><td><select name="scope"><option>global</option><option>link</option><option>host</option></select></td></tr> -->\
		</table>\
	</div>');
	$alconfig.find(".configure-method").selectmenu({
		width:	128,
	});
*/
	}
	var $li2 = $('<li><a href="#interface-system-settings">System Settings</a></li>');
	$('#interfaces ul').append($li2);
	var $systemsettings = $('<div class="interface"></div>');
	$('#interfaces').append($systemsettings);
	$systemsettings.attr('id', 'interface-system-settings');
	$systemSettingsAccordion = $('<div id="systemSettingsAccordion"></div>');
	$systemsettings.append($systemSettingsAccordion);
	build_system_settings(config, data, $systemSettingsAccordion);
	$("#interfaces").tabs();
	console.log($('#update').attr("disabled"))
	$('#update').button('enable');
	setTimeout(update_current_settings, 5000);
}).fail(function (XHR, status, error) {
	alert("Failed to get configuration information!");
});

</script>
</body>
</html>
