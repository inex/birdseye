<?php

namespace App\Http\Controllers\LookingGlass;

use App\Http\Controllers\LookingGlass\Controller as BaseController;

class Routes extends BaseController
{
    public function protocol($protocol) {
        return app()->make('view')->make('lg/routes')->with( [
            'content' => json_decode( app()->call('\App\Http\Controllers\Routes@protocol', ['protocol'=>$protocol])->content() ),
            'source' => 'protocol', 'name' => $protocol
        ] );
    }

    public function table($table) {
        return app()->make('view')->make('lg/routes')->with( [
            'content' => json_decode( app()->call('\App\Http\Controllers\Routes@table', ['table'=>$table])->content() ),
            'source' => 'table', 'name' => $table

        ] );
    }

    public function lookupProtocol($net,$protocol) {
        return app()->make('view')->make('lg/route')->with( [
            'content' => json_decode( app()->call('\App\Http\Controllers\Routes@lookupProtocol', ['net'=>$net,'protocol'=>$protocol])->content() ),
            'source' => 'protocol', 'name' => $protocol, 'net' => urldecode($net)
        ] );
    }

    public function lookupTable($net,$table) {
        return app()->make('view')->make('lg/route')->with( [
            'content' => json_decode( app()->call('\App\Http\Controllers\Routes@lookupTable', ['net'=>$net,'table'=>$table])->content() ),
            'source' => 'table', 'name' => $table, 'net' => urldecode($net)
        ] );
    }

    public function getLookup() {
        return app()->make('view')->make('lg/route-lookup')->with( [
            'content' => json_decode( app()->call('\App\Http\Controllers\Symbols@all' )->content() ),
        ] );
    }
}
