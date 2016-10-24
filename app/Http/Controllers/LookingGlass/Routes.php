<?php

namespace App\Http\Controllers\LookingGlass;

use App\Http\Controllers\LookingGlass\Controller as BaseController;

class Routes extends BaseController
{
    public function protocol($protocol) {
        return app()->make('view')->make('lg/routes')->with( [
            'routes' => json_decode( app()->call('\App\Http\Controllers\Routes@protocol', ['protocol'=>$protocol])->content() )
        ] );
    }

    public function table($table) {
        return app()->make('view')->make('lg/routes')->with( [
            'routes' => json_decode( app()->call('\App\Http\Controllers\Routes@table', ['table'=>$table])->content() )
        ] );
    }
}
