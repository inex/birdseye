#!/usr/bin/env bash

apt update

apt full-upgrade -y

apt install software-properties-common python-software-properties

add-apt-repository -yu ppa:cz.nic-labs/bird

apt install bird quagga joe

sleep 1

killall bird
killall bird6

# install packages for Birdseye
apt install -y lighttpd php-cgi php-mbstring php-xml

systemctl stop lighttpd.service
cp /vagrant/data/vagrant/lighttpd.conf /etc/lighttpd/
lighty-enable-mod fastcgi
lighty-enable-mod fastcgi-php
systemctl start lighttpd.service


# Useful screen settings for barryo:
cat >/home/ubuntu/.screenrc <<END_SCREEN
termcapinfo xterm* ti@:te@
vbell off
startup_message off
defutf8 on
defscrollback 2048
nonblock on
hardstatus on
hardstatus alwayslastline
hardstatus string '%{= kG}%-Lw%{= kW}%50> %n%f* %t%{= kG}%+Lw%<'
screen -t bash     0
altscreen on
END_SCREEN


echo -e "www-data        ALL=(ALL)       NOPASSWD: /vagrant/bin/birdc\n" >/etc/sudoers.d/99-birdseye

cp /vagrant/.env.vagrant /vagrant/.env

cd /vagrant/data/bird-lab/ipv4/
./start.sh
cd /vagrant/data/bird-lab/ipv6/
./start.sh

