<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\SlimService\Application;
use Slim\Http\Environment;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @return Environment
 */
$this['environment']  =  function () : Environment {
    $server = $_SERVER;
    if (!empty($server['HTTPS']) && strtolower($server['HTTPS']) !== 'off'
        // hide behind proxy / maybe cloud flare cdn
        || isset($server['HTTP_X_FORWARDED_PROTO'])
           && $server['HTTP_X_FORWARDED_PROTO'] === 'https'
        || !empty($server['HTTP_FRONT_END_HTTPS'])
           && strtolower($server['HTTP_FRONT_END_HTTPS']) !== 'off'
    ) {
        // detect if non standard protocol
        if ($server['SERVER_PORT'] == 80
            && (isset($server['HTTP_X_FORWARDED_PROTO'])
                || isset($server['HTTP_FRONT_END_HTTPS'])
            )
        ) {
            $server['SERVER_PORT'] = 443;
        }

        $server['HTTPS'] = 'on';
    }

    return new Environment($server);
};
