<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\BirdDummy;
use App\Bird;

class BirdServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton( 'Bird', function ($app) {
            if( env('USE_BIRD_DUMMY', false ) ) {
                return new BirdDummy;
            }

            if( !env('BIRDC',false) ) {  //}|| !is_executable( env('BIRDC') ) ) {
                abort( 500, "Birdc command not specified / executable" );
            }

            return new Bird( env('BIRDC'), env( 'CACHE_KEY' ) );
        });
    }
}
