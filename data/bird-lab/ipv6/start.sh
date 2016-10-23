#!/bin/bash

# add ips we need
/bin/ip addr add fc00::1/64 dev enp0s3
/bin/ip addr add fc00::111/64 dev enp0s3
/bin/ip addr add fc00::222/64 dev enp0s3
/bin/ip addr add fc00::244/64 dev enp0s3

mkdir -p /var/run/bird

/usr/sbin/bird6 -c rs.conf -s /var/run/bird/rs6.sock -P /var/run/bird/rs6.pid

/usr/lib/quagga/bgpd  -u quagga -f client-quagga-111.conf -i /var/run/quagga/client6-111 -z /var/run/quagga/client6-111.sock -l fc00::111 -A fc00::111 -nd
/usr/lib/quagga/bgpd  -u quagga -f client-quagga-222.conf -i /var/run/quagga/client6-222 -z /var/run/quagga/client6-222.sock -l fc00::222 -A fc00::222 -nd
/usr/lib/quagga/bgpd  -u quagga -f client-quagga-244.conf -i /var/run/quagga/client6-244 -z /var/run/quagga/client6-244.sock -l fc00::244 -A fc00::244 -nd
