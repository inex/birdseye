<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// just for testing
$router->get( 'test', function() {
    return "Hello, world!";
});


// This is pretty kack but fideloper/TrustedProxy seems to not work on Lumen yet
if( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ) {
    $proto = 'https://';
} else if( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) == 'https' ) {
    $proto = 'https://';
} else {
    $proto = 'http://';
}

$url = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $proto . $_SERVER['HTTP_X_FORWARDED_HOST'] : url( '', [], $proto === 'https://' );

// A proxy can optionally set HTTP_X_URL to add a prefix to the URL (http://www.example.com/prefix/).
if( isset($_SERVER['HTTP_X_URL'] ) ) {
    if( substr( $url, -1 ) == '/' && substr( $_SERVER['HTTP_X_URL'], 0, 1 ) == '/' ) {
        $url .= substr( $_SERVER['HTTP_X_URL'], 1 );
    } else if( substr( $url, -1 ) != '/' && substr( $_SERVER['HTTP_X_URL'], 0, 1 ) != '/' ) {
         $url .= '/' . $_SERVER['HTTP_X_URL'];
    } else {
         $url .=  $_SERVER['HTTP_X_URL'];
    }
}

// remove tailing slash
if( substr( $url, -1 ) == '/' ) {
    $url = substr( $url, 0, -1 );
}
$url = 'http://birdseye.test';

$router->get('/', function () use ($router,$url) {
    return $router->app->make('view')->make('index')->with( [
        'url'    => $url,
        'status' => json_decode( $router->app->call('\App\Http\Controllers\Status@show' )->content() )
    ]);
});

$router->get('api/status', 'Status@show');

$router->get('api/protocols/bgp', 'Protocols@bgp');
$router->get('api/protocol/{protocol}', 'Protocols@protocol');

$router->get('api/symbols', 'Symbols@all');
$router->get('api/symbols/tables', 'Symbols@tables');
$router->get('api/symbols/protocols', 'Symbols@protocols');

$router->get('api/routes/protocol/{protocol}', 'Routes@protocol' );
$router->get('api/routes/table/{table}',       'Routes@table'    );
$router->get('api/routes/export/{protocol}',   'Routes@export'   );

$router->get('api/routes/count/protocol/{protocol}', 'Routes@protocolCount' );
$router->get('api/routes/count/table/{table}',       'Routes@tableCount'    );
$router->get('api/routes/count/export/{protocol}',   'Routes@exportCount'   );

// Get wildcard large communities in protocol tabe of form ( x, y, * )
$router->get('api/routes/lc-zwild/protocol/{protocol}/{x}/{y}', 'Routes@protocolLargeCommunityWildXY' );


$throttle = env('THROTTLE_PER_MIN',20);

$router->group(['middleware' => 'throttle:' . $throttle,'namespace' => 'App\Http\Controllers'], function () use ($router) {
    $router->get('api/route/{net}',                     'Routes@lookupTable');
    $router->get('api/route/{net}/table/{table}',       'Routes@lookupTable');
    $router->get('api/route/{net}/protocol/{protocol}', 'Routes@lookupProtocol');
    $router->get('api/route/{net}/export/{protocol}',   'Routes@lookupExport');
});

if( env('LOOKING_GLASS_ENABLED', false ) ) {

    $router->group(['prefix' => 'lg', 'namespace' => 'App\Http\Controllers\LookingGlass'], function () use ($router,$url) {

        $router->make('view')->share('url',$url);
        $router->make('view')->share('status', json_decode( $router->app->call('\App\Http\Controllers\Status@show' )->content() ) );

        $router->get('', function() use ($router,$url) {
            return redirect( $url . '/lg/protocols/bgp' );
        });

        $router->get('protocols/bgp',              'Protocols\Bgp@summary' );
        $router->get('routes/protocol/{protocol}', 'Routes@protocol'       );
        $router->get('routes/table/{table}',       'Routes@table'          );
        $router->get('routes/export/{protocol}',   'Routes@export'         );

        $router->get('route',                           'Routes@getLookup'      );
        $router->get('route/{net}/protocol/{protocol}', 'Routes@lookupProtocol' );
        $router->get('route/{net}/table/{table}',       'Routes@lookupTable'    );
    });
}
