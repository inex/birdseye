#! /usr/bin/env php
<?php

/**
 * nagios-check-birdseye.php
 *
 * Nagios plugin to check a bird instance using the Bird's Eye API:
 *     https://github.com/inex/birdseye
 *
 * MIT License
 *
 * Copyright (c) 2016 Internet Neutral Exchange Association Limited by Guarantee
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

ini_set( 'max_execution_time', '55' );
ini_set( 'display_errors', true );
ini_set( 'display_startup_errors', true );

define( "STATUS_OK",       0 );
define( "STATUS_WARNING",  1 );
define( "STATUS_CRITICAL", 2 );
define( "STATUS_UNKNOWN",  3 );

define( "LOG__NONE",    0 );
define( "LOG__ERROR",   1 );
define( "LOG__VERBOSE", 2 );
define( "LOG__DEBUG",   3 );

// initialise some variables
$status    = STATUS_OK;
$log_level = LOG__NONE;

// possible output strings
$criticals = "";
$warnings  = "";
$unknowns  = "";
$normals   = "";

// set default values for command line arguments
$cmdargs = [
    'verbose' => false,
    'debug'   => false,
];

// parse the command line arguments
parseArguments();

if( !isset( $cmdargs['apihost'] ) || !is_string($cmdargs['apihost']) || ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $cmdargs['apihost'] ) ) {
    _log( "UNKNOWN: You must set a valid API host", LOG__ERROR );
    exit( STATUS_UNKNOWN );
}

$ch = curl_init($cmdargs['apihost'].'/status');
curl_setopt($ch, CURLOPT_HEADER, true);      // we want headers
curl_setopt($ch, CURLOPT_NOBODY, false);    // we need body
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_TIMEOUT,10);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if( $httpcode == 503 ) {
    // Bird's Eye could not query Bird
    echo "CRITICAL: Could not query Bird daemon\n";
    exit( STATUS_CRITICAL );
}

list($header, $body) = explode("\r\n\r\n", $response, 2);

$content = json_decode($body);

if( $content === null ) {
    echo "UNKNOWN: Invalid / no JSON returned from API endpoint. Check your URL.\n";
    exit( STATUS_UNKNOWN );
}

$normals .= "Bird " . $content->status->version . ". Bird's Eye " . $content->api->version . ". "
    . "Router ID " . $content->status->router_id . ". "
    . "Uptime: " . (new DateTime)->diff( DateTime::createFromFormat( 'Y-m-d\TH:i:sO', $content->status->last_reboot ) )->days . " days. "
    . "Last Reconfigure: " . DateTime::createFromFormat( 'Y-m-d\TH:i:sO', $content->status->last_reconfig )->format( 'Y-m-d H:i:s' ) . ".";

if( ( $bgpSum = json_decode( file_get_contents($cmdargs['apihost'].'/protocols/bgp') ) ) !== null ) {
    $total = 0;
    $up = 0;

    if( isset( $bgpSum->protocols ) ) {
        foreach( $bgpSum->protocols as $name => $p ) {
            if( $p->bird_protocol != 'BGP' ) {
                continue;
            }
            $total++;

            if( $p->state == 'up' ) {
                $up++;
            }
        }
    }
    
    $normals .= "{$up} BGP sessions up of {$total}.";
} else {
    setStatus( STATUS_WARNING );
    $warnings .= "Could not query BGP protocols.";
}


if( $status == STATUS_OK ) {
    $msg = "OK: {$normals}\n";
} else {
    $msg .= "{$criticals}{$warnings}{$unknowns}{$normals}\n";
}

switch( $status ) {
    case STATUS_CRITICAL:
        echo 'CRITICAL: ';
        break;
    case STATUS_WARNING:
        echo 'WARNING: ';
        break;
    case STATUS_UNKNOWN:
        echo 'UNKNOWN: ';
        break;
}
echo $msg;
exit( $status );


/**
 * Parses (and checks some) command line arguments
 */
function parseArguments()
{
    global $checkOptions, $cmdargs, $argc, $argv;
    if( $argc == 1 )
    {
        printUsage();
        exit( STATUS_UNKNOWN );
    }
    $i = 1;
    while( $i < $argc )
    {
        if( $argv[$i][0] != '-' )
        {
            $i++;
            continue;
        }
        switch( $argv[$i][1] )
        {
            case 'V':
                printVersion();
                exit( STATUS_OK );
                break;
            case 'v':
                $cmdargs['verbose'] = true;
                $i++;
                break;
            case 'd':
                $cmdargs['debug'] = true;
                $i++;
                break;
            case 'a':
                $cmdargs['apihost'] = $argv[$i+1];
                $i++;
                break;
            default:
                if( !isset( $argv[$i+1] ) || substr( $argv[$i+1], 0, 1 ) == '-' )
                    $cmdargs[ substr( $argv[$i], 2 ) ] = true;
                else
                    $cmdargs[ substr( $argv[$i], 2 ) ] = $argv[$i+1];
                $i++;
                break;
        }
    }
}

/**
 * Sets the planned exit status without overriding a previous error states.
 *
 * @param int $new_status New status to set.
 * @return void
 */
function setStatus( $new_status )
{
    global $status;
    if( $new_status > $status )
        $status = $new_status;
}

/**
 * Prints a given message to stdout (or stderr as appropriate).
 *
 * @param string $log The log message.
 * @param int $level The log level the user has requested.
 * @return void
 */
function _log( $log, $level )
{
    global $cmdargs;
    if( $level == LOG__ERROR )
        fwrite( STDERR, "$log\n" );
    else if( $level <= $cmdargs['log_level'] )
        print( $log . "\n" );
}

/**
 * Print script usage instructions to the stadout
 */
function printUsage()
{
    global $argv;
    $progname = basename( $argv[0] );
    echo <<<END_USAGE
{$progname} -a http://api.example.com/api [-V] [-v] [-d]

END_USAGE;
}

/**
 * Print version information
 */
function printVersion()
{
    global $argv;
    printf( basename( $argv[0] ) . " (Nagios Plugin)\n" );
    echo "Licensed under the MIT License\n\n";
    echo <<<LICENSE
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
LICENSE;
}
