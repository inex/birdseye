#!/usr/bin/env bash

apt update

apt full-upgrade -y

apt install software-properties-common python-software-properties

add-apt-repository -yu ppa:cz.nic-labs/bird

apt install bird

sleep 1

killall bird
killall bird6

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
