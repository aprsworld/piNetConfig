# piNetConfig

## netconfig.php

netconfig.php is the main HTTP interface to the netconfig PHP handling.  A 'GET' REQUEST to this will spit out the current network configuration (which is documented below) and a 'POST' REQUEST will update the configuration files on the system after validation and then reboot the system.  The format is always JSON.

## netconfig-validate.php

netconfig-validate.php is the validation code for the configuration which is used before updating the configuration file(s) of the system.

## netconfig-current.php

netconfig-current.php handles obtaining the current system settings and values by parsing `ip`, `iwconfig`, and such.

## netconfig-read.php

netconfig-read.php handles parsing Debian style 'interface' files.

## netconfig-write.php

netconfig-write.php handles writing the JSON "config" block passed in out to a Debain style 'interface' file.

## net.html

net.html is an HTML and JavaScript Web Interface to use to get current configuration and settings and then to update them.

## JSON Format

The json file is a collection of physical interfaces which then have the nested virtual interfaces (aliases, vlans, etc).  In addition it has a configuration object which contains the current network configuration.

In the root level of each physical interface object there are all the link level parameters such as the mtu, mac address, etc.  The link level statistics such as packets and bytes sent and received will be added in the future.  Any members that are objects are virtual interfaces (The logical or virtual interfaces will always begin with the physical interfaces name).  This contains there current settings of each logical interface with each networking protocol settings being contained in the protocol and then the appropriate protocol (inet, inet6, etc) objects.  The config object is a pretty closing mapping of the /etc/network/interfaces file of debian.  At this time /etc/network/interfaces is the only changable file, and any source or source-directory directives will be parsed and held in the subobject "system".  These should be considered read-only and one should take care not to attempt to override anything in those as that results in undefined behavior.  An example of a standard one ipv4 ethernet interfaces is below:

```
{ "eth0": {
  "mtu": "1500",
  "hwaddress": "XX:XX:XX:XX:XX:XX",
  "state": "UP",
  "mode": "DEFAULT",
  ...
  "eth0": {
    "inet": {
      "address": "192.168.0.100",
      "netmask": "255.255.255.0",
      "gateway": "192.168.0.1",
      ...
    }
  },
  ...
  "config": {
    "eth0": {
      "auto": true,
      "allow": ["hotplug"],
      "protocol": {
        "inet": {
          "method": "dhcp"
        }
      }
   }
   ...
}
```

Any physical interface that is a wireless interface will contain a "wireless" subobject in it with the current wireless parameters.  (This currently needs to be cleaned up)


## 'interfaces' documentation

For existing documentation on the interfaces file see `man interfaces`, https://wiki.debian.org/NetworkConfiguration, or Google.  Unfortunately the network configuration is split up into seperate packages so documentation is split up in various other places and depending on system version and configuration different options may be present.  This implementation assumes the 'wpa-supplicant' package is installed and used for wireless configuration.  It's documentation is located most commonly in '/usr/share/doc/wpasupplicant' and '/usr/share/doc/wpa_supplicant' as well as attempting to Google.

This implementation currently only handles the ipv4 protocol named inet in the interfaces file.  It will only validate 'static' and 'dhcp' methods of configuration for the 'inet' block.  Everything else is not validated.

For 'static' configuration of the 'inet' block, you must specify an 'address' and 'netmask'.  The 'gateway' is validated if present.  For 'dhcp', nothing is validated.

All 'auto' declarations are converted to 'allow-auto' declarations.

As a good rule of thumb, when adding an interfaces to the configuration block always add `"allow": [ "auto", "hotplug" ]` to ensure that interface is brought up on boot.

For wireless configuration `"wpa-scan-ssid": "1"` and `"wpa-ap-scan": "1"` should always be added to ensure auto-reconnecting on lost wireless link and hidden ssid's are connected to.

As wpa-supplicant is used for wireless configuration it's block should look like:

```
{ "config": {
	...
	"wlan0": {
		"allow": ["auto", "hotplug" ],
		"protocol": {
			"inet" {
				"method": "dhcp",
				"wpa-scan-ssid": "1",
				"wpa-ap-scan": "1",
				"wpa-ssid": ...,
				"wpa-key-mgmt": ...,
				"wpa-psk": "\"...\""
			}
		}
	},
	...
}
}
```

'method' can also be static (or anything else).  'wpa-ssid' is obviously the ssid of the network to connect to.  'wpa-key-mgmt' is either 'WPA-PSK' for standard WPA/WPA2 secured networks or 'NONE' for an open access point.

For WEP wireless encryption the 'wireless-tools' are used and documentation is available in '/usr/share/doc/wireless-tools'.  It's configuration block should look like:

```
... "inet": {
	"method": "dhcp",
	"wireless-essid": ...,
	"wireless-key1": ...,
	...
```

Where 'wireless-essid' is the wireless network name and 'wireless-key1' is the hexidecimal key for WEP.  You can replace 1 with 2, 3, or 4 to enter those keys if desired.

