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
            return new Bird;
        });
    }
}
