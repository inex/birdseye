<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../version.php';

try {
    // we want to determine various settings based on host so
    // we can query multiple instances of bird on the same server

    if( isset( $_SERVER['BIRDSEYE_ENV_LOCATION']) ) {
        $envpath = $_SERVER['BIRDSEYE_ENV_LOCATION'];
    } else {
        $envpath = realpath( __DIR__.'/..' );
    }

    if( isset( $_SERVER['HTTP_X_BIRDSEYE'] ) ) {
        $name = $_SERVER['HTTP_X_BIRDSEYE'];
    } else if( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_HOST'], '.' ) ) {
        $name = explode( '.', $_SERVER['HTTP_X_FORWARDED_HOST'] )[0];
    } else if( isset( $_SERVER['HTTP_HOST'] ) && strpos( $_SERVER['HTTP_HOST'], '.' ) ) {
        $name = explode( '.', $_SERVER['HTTP_HOST'] )[0];
    }

    if( isset( $name ) ) {
        // a little sanity check on $name:
        if( !preg_match( '/^[a-zA-Z0-9_\-]+$/', $name ) ) {
            abort( 500, 'Bad hostname - see bootstrap/app.php' );
        }
        $envfile = 'birdseye-' . $name . '.env';
    }


    if( isset( $envfile ) && file_exists( $envpath.'/'.$envfile ) && is_readable($envpath.'/'.$envfile) ) {
        $_ENV['BIRDSEYE_ENV_FILE'] = $envpath.'/'.$envfile;
        $_ENV['BIRDSEYE_CACHE_KEY'] = $name . '::';
        (new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
            $envpath, $envfile
        ))->bootstrap();
    }else {
        $_ENV['BIRDSEYE_ENV_FILE'] = '.env';
        $_ENV['BIRDSEYE_CACHE_KEY'] = env('BIRDSEYE_CACHE_KEY', 'SomeCacheKey');
        (new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
            dirname(__DIR__)
        ))->bootstrap();
    }

    if( php_sapi_name() !== 'cli' && ( !isset( $_ENV['BIRDSEYE_CACHE_KEY'] ) || !strlen( $_ENV['BIRDSEYE_CACHE_KEY'] ) ) ) {
        header('HTTP/1.1 500 Cache key not specified');
        exit;
    }
} catch (\Exception) {
    header('HTTP/1.1 500 Configuration issue - see bootstrap/app.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);



/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

//$app->configure('app');


/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

$app->routeMiddleware([
    'throttle' => App\Http\Middleware\ThrottleRequests::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\BirdServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
