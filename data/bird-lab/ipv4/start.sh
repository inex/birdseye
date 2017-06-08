#!/bin/bash

# add ips we need
/bin/ip addr add 192.0.2.100/24 dev lo
/bin/ip addr add 192.0.2.111/24 dev lo
/bin/ip addr add 192.0.2.222/24 dev lo
/bin/ip addr add 192.0.2.244/24 dev lo

mkdir -p /var/run/bird

/usr/sbin/bird -c rs.conf -s /var/run/bird/rs.sock -P /var/run/bird/rs.pid

/usr/lib/quagga/bgpd  -u quagga -f client-quagga-111.conf -i /var/run/quagga/client-111 -z /var/run/quagga/client-111.sock -l 192.0.2.111 -A 192.0.2.111 -nd
/usr/lib/quagga/bgpd  -u quagga -f client-quagga-222.conf -i /var/run/quagga/client-222 -z /var/run/quagga/client-222.sock -l 192.0.2.222 -A 192.0.2.222 -nd
/usr/lib/quagga/bgpd  -u quagga -f client-quagga-244.conf -i /var/run/quagga/client-244 -z /var/run/quagga/client-244.sock -l 192.0.2.244 -A 192.0.2.244 -nd
