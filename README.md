# piNetConfig

## current_settings.php

current_settings.php spits out a json file with the current network settings and the current network configuration.

The json file is a collection of physical interfaces which then have the nested virtual interfaces (aliases, vlans, etc).  In addition it has a configuration object which contains the current network configuration.

In the root level of each physical interface object there are all the link level parameters such as the mtu, mac address, etc.  The link level statistics such as packets and bytes sent and received will be added in the future.  Any members that are objects are virtual interfaces (at this time they are all named starting with the name of the physical interface).  This contains there current settings of each virtual interface with each networking protocol settings being contained in the protocol and then the appropriate protocol (inet, inet6, etc) objects.  The config object is a pretty closing mapping of the /etc/network/interfaces file of debian.  An example of a standard one ipv4 ethernet interfaces is below:

...
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
...

## validate.php

validate.php contains the configuration validation code to ensure that valid settings are given before updating the configuration file and rebooting.


## netconfig.php

netconfig.php contains the code to parse the various network configuration files including /etc/network/interfaces.

## netconfig-write.php

netconfig-write.php contains the code to replace the various network configuration files.


## net.html

net.html is the web interface to these scripts.
