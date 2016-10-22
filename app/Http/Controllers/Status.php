<?php

namespace App\Http\Controllers;

class Status extends Controller
{
    public function show()
    {
        $status = app('Bird')->status();

        return $this->verifyAndSendJSON( $status );
    }
}
