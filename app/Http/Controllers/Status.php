<?php

namespace App\Http\Controllers;

use Cache;

class Status extends Controller
{
    public function show()
    {
        if( $status = Cache::get( $this->cacheKey() . 'status' ) ) {
            $api['from_cache'] = true;
        } else {
            $status = app('Bird')->status();
            Cache::put($this->cacheKey() . 'status', $status, env( 'CACHE_SHOW_STATUS', 1 ) );
            $api['from_cache'] = false;
        }

        return $this->verifyAndSendJSON( 'status', $status, $api );
    }
}
