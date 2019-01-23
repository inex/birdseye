#!/usr/bin/env bash

apt update

apt full-upgrade -y

apt install -y software-properties-common python-software-properties

apt install -y build-essential libssh-dev libreadline-dev libncurses-dev flex bison quagga joe checkinstall

sleep 1

cd /usr/src
wget ftp://bird.network.cz/pub/bird/bird-2.0.3.tar.gz
tar zxf  bird-2.0.3.tar.gz
cd bird-2.0.3/
./configure  --prefix=/usr --sysconfdir=/etc
make -j2
checkinstall -y

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

