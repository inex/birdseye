# Bird's Eye - A Simple Secure Micro Service for Querying BIRD

A simple **secure** PHP micro service to provide some BIRD protocol / routing information via a HTTP API as JSON, with an optional built-in looking-glass implementation.

This is the winning project from the [RIPE IXP Tools Hackaton](https://atlas.ripe.net/hackathon/ixp-tools/) just prior to [RIPE73](https://ripe73.ripe.net/) in Madrid, Spain. Since the hackathon, substantial improvements have been made.

The end of workshop presentation can be found here: [[Keynote](https://dl.dropboxusercontent.com/u/42407394/2016-10-RIPE73-IXP-Tools/2016-10-RIPE73-IXP-Tools-BirdsEye.key)] [[PDF](https://dl.dropboxusercontent.com/u/42407394/2016-10-RIPE73-IXP-Tools/2016-10-RIPE73-IXP-Tools-BirdsEye.pdf)]. A more detailed presentation to the [Open Source Working Group](https://ripe73.ripe.net/programme/meeting-plan/os-wg/) at RIPE73, delivered by @nickhilliard, can be found here: [[VIDEO](https://ripe73.ripe.net/archives/video/1505)]

Author: [Barry O'Donovan](https://www.barryodonovan.com/contact), [INEX](https://www.inex.ie/), Dublin, Ireland


## Live Examples

INEX runs a number of BIRD instances and many of them have a public looking glass powered by Bird's Eye as a standalone live example and also integrated with IXP Manager as a frontend consumer.

* INEX Cork IPv4 Router Collector: https://www.inex.ie/rc1-cork-ipv4/
* INEX Cork IPv6 Router Collector: https://www.inex.ie/rc1-cork-ipv6/

The landing pages for the above also document the API calls available.

You can see the IXP Manager integration for ~30 BIRD daemons at INEX at https://www.inex.ie/ixp/lg. [GR-IX](https://www.gr-ix.gr/) have also a public IXP Manager integration at: https://portal.gr-ix.gr/lg.

## Complementary Projects

At the hackathon, the team also produced consumers of this API:

* https://github.com/dfkbg/birdseye - Python CLI consumer by Daniel Karrenberg
* https://github.com/ecix/birdseye - Python based web consumer by Annika Hannig


## Rationale

Historically, IXPs made route collector and route server information available via looking-glasses. Over the past few years, many IXPs have selected BIRD as their route server / collector BGP daemon for a number of good reasons.

BIRD lacks an API to allow IXPs to provide these looking glass type tools. Moreover, this also affects an IXP's ability to monitor these routing daemons and peering participant sessions to them.

In a typical IXP, there will be six daemons per peering LAN:

 * two route servers and one route collector
 * separate daemon for ipv4 and ipv6

Having looked at existing BIRD LG implementations, INEX could not identify one that was designed with security in mind.  Bird's Eye was written with security as a primary consideration.

## Security

As this is intended to be deployed on an IXP's route server / route collector, security is vital. In that regard, the following considerations have been made:

* Natural rate limiting via caching by default. All queries are cached for a (configurable) period of at least one minute. This means the most you can hit the BIRD daemon for a specific request is once per minute.
* Built in rate limiter for queries that take variable parameters (e.g. route lookup).
* Strict parameter parsing and checking.
* Bundled `birdc` bash script for safe use via sudo - the web process will require this to access the BIRD socket.
* `birdc` executed in *restricted* mode, to allow ''show'' commands only.

This API was not designed with the notion of making it publicly available.  Ideally it would be run on an internal private network and fronted by one of the looking glass frontends above that consume this API, thereby providing multiple levels of API parameter validation.

## Outlook

In an ideal world, this micro-service will be deprecated once the BIRD developers release a version with a built-in HTTP JSON API. This is a (hopefully) temporary solution to plug a gap.

## Installation

This is a basic [Lumen](https://lumen.laravel.com/) PHP application and the requirements are:

* PHP >= 8.1
* Mbstring PHP Extension

Download the release package **(ensure you get the latest version rather than the example version listed below!)** and install on your server. E.g.:

```sh
# E.g. Ubuntu 20.04 / 22.04 / 24.04 LTS
apt-get install php-cgi php-mbstring php-xml unzip
cd /srv
wget https://github.com/inex/birdseye/releases/download/vx.y.z/birdseye-vx.y.z.tar.bz2
tar jxf birdseye-vx.y.z.tar.bz2
cd birdseye-vx.y.z
chown -R www-data: storage  # or the appropriate web user on your system
```

You'll need a web server to front it. Apache or Lighttpd are good choices. As the requirements are small and you most likely don't have any other use for a web server on the route server / collector boxes, Lighttpd has a small footprint:

```sh
apt-get install lighttpd
lighty-enable-mod fastcgi
lighty-enable-mod fastcgi-php
```

And configure Lighttpd - see [data/configs/lighttpd.conf](https://github.com/inex/birdseye/blob/master/data/configs/lighttpd.conf) for an example.

### Install from Source with Composer

If you prefer to install from source [with composer](https://getcomposer.org/download/):

```sh
git clone https://github.com/inex/birdseye.git
cd birdseye
composer install --prefer-dist --no-dev
chown -R www-data: storage  # or the appropriate web user on your system
```

## Configuration

We have tried to make configuration as easy as possible while allowing for the fact that there will typically be at least two BIRD processes to query on the same server. An explanation is easiest with an example:

Let's say there is a route server called `rs1.inex.ie` which provides both IPv4 and IPv6 services to two separate peering LANs.

To query the individual four daemons, we create DNS aliases as follows:

```
rs1-lan1-ipv4.inex.ie IN CNAME rs1.inex.ie
rs1-lan1-ipv6.inex.ie IN CNAME rs1.inex.ie
rs1-lan2-ipv4.inex.ie IN CNAME rs1.inex.ie
rs1-lan2-ipv6.inex.ie IN CNAME rs1.inex.ie
```

The micro-service will extract the first element of the hostname (e.g. `rs1-lan1-ipv4`, see beginning of `bootstrap/app.php`) and look for an environment file in the applications root directory (e.g. `/srv/birdseye`) named as follows for the above examples:

```
rs1-lan1-ipv4.inex.ie -> /srv/birdseye/birdseye-rs1-lan1-ipv4.env
rs1-lan1-ipv6.inex.ie -> /srv/birdseye/birdseye-rs1-lan1-ipv6.env
rs1-lan2-ipv4.inex.ie -> /srv/birdseye/birdseye-rs1-lan2-ipv4.env
rs1-lan2-ipv6.inex.ie -> /srv/birdseye/birdseye-rs1-lan2-ipv6.env
```

To create your env file, just (following the above naming convention):

```
cd /srv/birdseye
cp .env.example birdseye-rs1-lan1-ipv4.env
```

If you do not want to use hostnames and your Bird's Eye installation is behind a proxy, you can set the same element as above in the HTTP request header: `X-BIRDSEYE`. See the Varnish example below in the *Serving Behind a Proxy* section.

This example file has sane defaults but you need to edit it and fix the `BIRDC` parameter. In our naming case above (and for `rs1-lan1-ipv4.inex.ie`) and with Bird v1.x.y we'd set it to:

```
BIRDC="/usr/bin/sudo /srv/birdseye/bin/birdc -4 -s /var/run/bird/rs1-lan1-ipv4.ctl"
```

with the assumption that we've named and located the BIRD socket at that location. If you are using Bird v1.x.y with the IPv6 daemon, change the `-4` switch to `-6`. From Bird's Eye v1.2.0 onwards, we now also support Bird v2; in this case use the `-2` switch. 

If you have a single BIRD daemon, you can skip DNS and just do:

```sh
cp .env.example .env
```

The last thing you need to do is give the `www-data` (or the appropriate web server user) user permission to run the `birdc` script. Edit `/etc/sudoers` and add (example):

```
www-data        ALL=(ALL)       NOPASSWD: /srv/birdseye/bin/birdc
```

## Built in Looking Glass

This API has an optional built-in looking glass which utilises the API internally. This is mildly inefficient as it means a json_encode/json_decode of the same data but it proves the API, keeps us honest and the performance overhead is not excessive.

To enable it, set the following parameter to true in your configuration file:

```
LOOKING_GLASS_ENABLED=true
```

This will activate the looking glass routes, add a link to the header and make the looking glass available under the base URL `/lg`.

## Disabling Caching on a Per-Request Basis

Caching was implemented to provide a natural rate-limiting mechanism for security and to reduce the load on BIRD.

In environments where you already have security in place (e.g. authenticated users on IXP Manager), you may want to disable caching for those requests. You can whitelist a set of IP addresses for this purpose by:

```sh
cp skipcache_ips.php.dist skipcache_ips.php
```

and then editing `skipcache_ips.php` to add your (for example) IXP Manager server's IP address.

If you then tag `?use_cache=0` to API requests, the cache will be avoided. Note that the results from BIRD will still be added to the cache so standard requests will still benefit with the freshest data.

## Serving Behind a Proxy

The API requires prefixes (e.g. `192.0.2.0/24`) to be submitted as GET requests and so they need to be URL encoded. Some web servers such as Apache squash these. A sample Apache configuration for proxying Bird's Eye requests is:

```
<VirtualHost 192.0.2.17:80 [2001:db8::17]:80>
    ServerName rc1-lan1-ipv4.example.com
    ServerAlias rc1-lan1-ipv6.example.com

    AllowEncodedSlashes NoDecode

    ProxyPass               /       http://10.8.5.126/     nocanon
    ProxyPassReverse        /       http://10.8.5.126/
</VirtualHost>
```

The code to work out what URL should be used in links [can be seen here](https://github.com/inex/birdseye/blob/master/app/Http/routes.php). In essence:

* if the server is configured for HTTPS, then `https://` is forced.
  * otherwise if `$_SERVER['HTTP_X_FORWARDED_PROTO']` (`X-Forwarded-Proto` in the HTTP request header) is `https`, then `https://` is forced.
  * otherwise it is `http://`
* if `$_SERVER['HTTP_X_FORWARDED_HOST']` (`X-Forwarded-Host` in the HTTP request header) is set, then that host is used in the URL. Otherwise it is worked out by PHP in the normal manner.
* if `$_SERVER['HTTP_X_URL']` (`X-Url` in the HTTP request header) is set, then that is appended to the hostname as a path / URL prefix.

For example, the live demos above (such as https://www.inex.ie/rc1-cork-ipv4/) is served via a Varnish proxy to an internal host with the following Varnish configuration:

```
backend rc1_cork_ipv4 {
    .host = "rc1-ipv4.cork.inex.ie";
    .port = "80";
}

sub vcl_recv {
    ...
    # Birdseye Example Sites
    if (req.url ~ "^/rc1-cork-ipv4/?" ) {
        set req.backend_hint = rc1_cork_ipv4;
        set req.http.x-url = "/rc1-cork-ipv4";
        set req.http.x-birdseye = "rc1-ipv4";
        set req.url = regsub( req.url, "^/rc1-cork-ipv4/?", "/");
        return(pass);
    }
}
```

When a request to Varnish for https://www.inex.ie/rc1-cork-ipv4/ hits the internal host, it sees a HTTP request with with following in PHP's `$_SERVER` array:

```
GET / HTTP/1.0
HTTP_HOST: www.inex.ie
HTTP_X_FORWARDED_PROTO: https
HTTP_X_URL: /rc1-cork-ipv4
HTTP_X_BIRDSEYE: rc1-ipv4
```        

Using the above logic, the resolves to a base URL of: `https://www.inex.ie/rc1-cork-ipv4`.

## Nagios Plugins

There are two basic Nagios plugins in the the `bin/` directory which can be downloaded to your Nagios server. As of October 2016, these were in production use at [INEX](https://www.inex.ie/) monitoring 24 BIRD daemons and ~350 route server sessions.

The first monitors the basic status of the BIRD daemon:

```
# BIRD daemon stopped:
$ ./nagios-check-birdseye.php -a http://rc1q-cix-ipv4.inex.ie/api
CRITICAL: Could not query Bird daemon
$ echo $?
2
# BIRD daemon running:
$ ./nagios-check-birdseye.php -a http://rc1q-cix-ipv4.inex.ie/api
OK: Bird 1.5.0. Bird's Eye 1.0.0. Router ID 185.1.69.126. Uptime: 0 days. Last Reconfigure: 2016-10-26 11:22:35. 0 BGP sessions up of 0.
$ echo $?
0
```

The second will monitor BGP session states (all or a named protocol):

```
$ ./nagios-check-birdseye-bgp-sessions.php -a http://rc1-cix-ipv4.inex.ie/api
OK: 11/11 BGP sessions up.
$ echo $?
0
$ ./nagios-check-birdseye-bgp-sessions.php -a http://rc1-cix-ipv6.inex.ie/api
CRITICAL: Protocol pb_as42090_vli239_ipv6 - AS42090 down (152 days). 8/9 BGP sessions up.
$ echo $?
2
```

You can disable the Team Cymru ASN to ASN name resolution above (via DNS) by setting the `-n` option:

```
$ ./nagios-check-birdseye-bgp-sessions.php -a http://rc1-cix-ipv6.inex.ie/api -n
CRITICAL: Protocol pb_as42090_vli239_ipv6 - AS42090 down (152 days). 8/9 BGP sessions up.
```

And you can query a specific protocol with:

```
$ ./nagios-check-birdseye-bgp-sessions.php -a http://rc1-cix-ipv4.inex.ie/api -p pb_as43760_vli226_ipv4
OK: BGP session pb_as43760_vli226_ipv4 with AS43760 [INEX-RS Internet Neutral Exchange Association Limited, IE] up (197 days)
```

This also performs prefix limit checking and warning at 80% by default:

```
$ ./nagios-check-birdseye-bgp-sessions.php -a http://birdseye.dev/api
WARNING: BGP session pb_0182_as61337 with AS61337 [ECOM-AS, GB] up (161 days) but prefix limit at 46/50. BGP session pb_0160_as199256 with AS199256 [LTH-AS , IE] up (161 days) but prefix limit at 18/20. 69/73 BGP sessions up.
$ echo $?
1
```

Prefix limit checking can be disabled with a `-l` option.

## BIRD Configuration File

Before version v1.2.0, Bird's Eye used the default timeformat settings in Bird.

From v1.2.0 onwards, Bird's Eye expects timestamps to be presented in ISO 8601 format.  This
needs to be configured in the BIRD configuration file using the following
statements:

```
# Use ISO 8601 time formats:
timeformat base         iso long;
timeformat log          iso long;
timeformat protocol     iso long;
timeformat route        iso long;
```

## Vagrant / Development

This repository includes a [Vagrant](https://www.vagrantup.com/) file with a
[bootstrap script](https://github.com/inex/birdseye/blob/master/Vagrant-bootstrap.sh) which,
on first run of `vagrant up` will:

* Boot an instance of Ubuntu 16.04 LTS.
* Update / upgrade all packages.
* Installs latest stable BIRD from [CZ.NIC's Ubuntu PPA](https://launchpad.net/~cz.nic-labs) (at
time of writing this is 1.6.3 with Large BGP Community support).
* Installs and configures Lighttpd ([config](https://github.com/inex/birdseye/blob/master/data/vagrant/lighttpd.conf)).
* Spins up the lab IPv4 and IPv6 environments (see `start.sh` scripts in `data/bird-lab/ipv[46]`).

Bird's Eye should now be accessible on: http://localhost::8088/

To switch to the IPv6 lab, swap the `BIRDC` option in the `.env` file (the IPv6 version is commented out).


## License

This application is open-sourced software licensed under the MIT license - see [the license file](LICENSE.md).
