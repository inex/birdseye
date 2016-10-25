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

// This is pretty kack but fideloper/TrustedProxy seems to not work on Lumen yet
$proto = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ? 'https://' : 'http://';
$url = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $proto . $_SERVER['HTTP_X_FORWARDED_HOST'] : url();


$app->get('/', function () use ($app,$url) {
    return $app->make('view')->make('index')->with( [
        'url'    => $url,
        'status' => json_decode( $app->call('\App\Http\Controllers\Status@show' )->content() )
    ]);
});

$app->get('api/status', 'Status@show');

$app->get('api/protocols/bgp', 'Protocols@bgp');
$app->get('api/protocol/{protocol}', 'Protocols@protocol');

$app->get('api/symbols', 'Symbols@all');
$app->get('api/symbols/tables', 'Symbols@tables');
$app->get('api/symbols/protocols', 'Symbols@protocols');

$app->get('api/routes/protocol/{protocol}', 'Routes@protocol');
$app->get('api/routes/table/{table}', 'Routes@table');

$app->get('api/routes/count/protocol/{protocol}', 'Routes@protocolCount');
$app->get('api/routes/count/table/{table}', 'Routes@tableCount');

$throttle = env('THROTTLE_PER_MIN',20);

$app->group(['middleware' => 'throttle:' . $throttle,'namespace' => 'App\Http\Controllers'], function () use ($app) {
    $app->get('api/route/{net}',                     'Routes@lookupTable');
    $app->get('api/route/{net}/table/{table}',       'Routes@lookupTable');
    $app->get('api/route/{net}/protocol/{protocol}', 'Routes@lookupProtocol');
});

if( env('LOOKING_GLASS_ENABLED', false ) ) {

    $app->group(['prefix' => 'lg', 'namespace' => 'App\Http\Controllers\LookingGlass'], function () use ($app,$url) {

        $app->make('view')->share('url',$url);
        $app->make('view')->share('status', json_decode( $app->call('\App\Http\Controllers\Status@show' )->content() ) );

        $app->get('', function() use ($app) {
            return redirect( '/lg/protocols/bgp' );
        });

        $app->get('protocols/bgp',              'Protocols\Bgp@summary' );
        $app->get('routes/protocol/{protocol}', 'Routes@protocol'       );
        $app->get('routes/table/{table}',       'Routes@table'          );

        $app->get('route',                           'Routes@getLookup'      );
        $app->get('route/{net}/protocol/{protocol}', 'Routes@lookupProtocol' );
        $app->get('route/{net}/table/{table}',       'Routes@lookupTable'    );
    });
}
