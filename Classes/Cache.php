<?php

namespace WapplerSystems\Proxy;

abstract class Cache {


    public function has($url) {
        $key = md5($url);

    }


}
